<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ProductData;
use App\Repository\ProductDataRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use DateTime;

class ProductImportService
{
    /**
     * @var ProductDataRepository
     */
    private $productDataRepository;

    private const CSV_SEPARATOR = ',';

    private const CSV_COLUMN_PRODUCT_CODE = 0;
    private const CSV_COLUMN_PRODUCT_NAME = 1;
    private const CSV_COLUMN_PRODUCT_DESCRIPTION = 2;
    private const CSV_COLUMN_STOCK = 3;
    private const CSV_COLUMN_COST = 4;
    private const CSV_COLUMN_DISCONTINUED = 5;

    /**
     * ProductImportService constructor.
     * @param ProductDataRepository $productDataRepository
     */
    public function __construct(ProductDataRepository $productDataRepository)
    {
        $this->productDataRepository = $productDataRepository;
    }

    /**
     * @param string|null $filePath
     * @param bool $ignoreFirstLine
     * @return array
     * @throws Exception
     */
    public function importFromCSV(?string $filePath, bool $ignoreFirstLine = true): array
    {
        if (empty($filePath)) {
            throw new Exception('Path to CSV file is required and must be valid', 11);
        }
        $filePath = str_replace("\\", '/', $filePath);
        if (!is_file($filePath)) {
            throw new Exception('Invalid path to file', 12);
        }
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            throw new Exception('Unable to open the file of "'.$filePath.'" for reading', 14);
        }

        $imported = [];

        $i = 0;
        while (($data = fgetcsv($handle, null, self::CSV_SEPARATOR)) !== FALSE) {
            $i++;
            if ($ignoreFirstLine && $i == 1) { continue; }
            $imported[] = $data;
        }
        fclose($handle);

        return $this->filterImportedProducts($imported);
    }

    /**
     * @param array $data
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeToDatabase(array $data): void
    {
        foreach ($data as $row) {
            $productData = new ProductData();
            $productData->setProductCode($row[self::CSV_COLUMN_PRODUCT_CODE]);
            $productData->setProductName($row[self::CSV_COLUMN_PRODUCT_NAME]);
            $productData->setProductDesc($row[self::CSV_COLUMN_PRODUCT_DESCRIPTION]);
            $productData->setStock((int) $row[self::CSV_COLUMN_STOCK]);
            $productData->setCost((float) $row[self::CSV_COLUMN_COST]);
            $productData->setDiscontinued(
                (mb_strtolower(trim($row[self::CSV_COLUMN_DISCONTINUED])) === mb_strtolower('yes')) ?
                    new DateTime() :
                    null
            );
            $this->productDataRepository->preSave($productData);
        }
        $this->productDataRepository->save();
    }

    /**
     * @param array $data
     * @return array
     */
    private function filterImportedProducts(array $data): array
    {
        $info = [
            'total_rows_qty' => count($data),
            'rows_successfully_imported' => 0,
            'rows_skipped' => 0,
            'skipped_row_numbers' => [],
            'skipped_rows_content' => [],
            'filtered_rows' => [],
        ];

        $data = array_map(function ($row) {
            if (!array_key_exists(self::CSV_COLUMN_STOCK, $row)) {
                $row[self::CSV_COLUMN_STOCK] = 0;
            }
            if (!array_key_exists(self::CSV_COLUMN_COST, $row)) {
                $row[self::CSV_COLUMN_COST] = 0.00;
            }
            return $row;
        }, $data);

        foreach ($data as $i => $row) {
            if (
                ($row[self::CSV_COLUMN_COST] < 5 && $row[self::CSV_COLUMN_STOCK] < 10) ||
                ($row[self::CSV_COLUMN_COST] > 1000) ||
                (count($row) > 6)
            ) {
                $info['skipped_row_numbers'][] = $i;
                $info['skipped_rows_content'][] = $row;
                $info['rows_skipped']++;
            }
        }

        $info['filtered_rows'] = array_values(array_filter($data, function ($row) {
            return !(
                ($row[self::CSV_COLUMN_COST] < 5 && $row[self::CSV_COLUMN_STOCK] < 10) ||
                ($row[self::CSV_COLUMN_COST] > 1000) ||
                (count($row) > 6));
        }));
        $info['filtered_rows'] = $this->excludeDuals($info);
        $info['rows_successfully_imported'] = $info['total_rows_qty'] - $info['rows_skipped'];

        return $info;
    }

    private function excludeDuals(array &$info): array
    {
        $data = $info['filtered_rows'];
        $info['dual_row_numbers'] = [];
        $dataWithoutDuals = [];
        for ($i = 0; $i < count($data); $i++) {
            for ($j = $i + 1; $j < count($data); $j++) {
                if ($data[$i][self::CSV_COLUMN_PRODUCT_CODE] === $data[$j][self::CSV_COLUMN_PRODUCT_CODE]) {
                    $info['dual_row_numbers'][] = $j;
                    $info['skipped_row_numbers'][] = $j;
                    $info['skipped_rows_content'][] = $data[$j];
                    $info['rows_skipped']++;
                }
            }
            if (!in_array($i, $info['dual_row_numbers'])) {
                $dataWithoutDuals[] = $data[$i];
            }
        }
        return $dataWithoutDuals;
    }
}
