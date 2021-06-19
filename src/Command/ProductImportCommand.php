<?php

namespace App\Command;

use App\Service\ProductImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProductImportCommand extends Command
{
    protected static $defaultName = 'app:product:import';

    /**
     * @var ProductImportService
     */
    private $productImportService;

    public function __construct(ProductImportService $productImportService, string $name = null)
    {
        $this->productImportService = $productImportService;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Imports products data from CSV file')
            ->setHelp('This command allows you to import products data from comma-separated CSV file')
            ->addOption(
                'filepath',
                'f',
                InputOption::VALUE_REQUIRED,
                'Path to comma-separated CSV-file you are going to import'
            )
            ->addOption(
                'test',
                't',
                null,
                'Reads data from CSV-file, but does not store in database table'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $filePath = $input->getOption('filepath');
            if (empty($filePath)) {
                $filePath = $io->ask('Path to CSV-file', null, function ($path) {
                    if (empty($path)) {
                        throw new Exception('Path to CSV file is required and must be valid', 11);
                    }

                    return (string) $path;
                });
            }
            $io->title('Import from CSV-file...');
            $startTime = microtime(true);
            $info = $this->productImportService->importFromCSV($filePath);
            $endTime = microtime(true);
            $csvImportExecTime = $endTime - $startTime;
            $dbStoreTime = 0;
            if (((int) $input->getOption('test')) === 0) {
                $isTestMode = $io->ask('Add imported data to database?', 'yes');
                if (in_array(mb_strtolower($isTestMode), ['y', 'yes'])) {
                    $startTime = microtime(true);
                    $this->productImportService->storeToDatabase($info['filtered_rows']);
                    $endTime = microtime(true);
                    $dbStoreTime = $endTime - $startTime;
                }
            }

            $io->text('CSV-file metadata:');
            $csvImportExecTime += $dbStoreTime;
            $io->table(
                ['CSV-file metadatum', 'value'],
                [
                    ['Import execution time (sec.):', $csvImportExecTime,],
                    ['Total rows quantity:', $info['total_rows_qty']],
                    ['Imported successfully:', $info['rows_successfully_imported']],
                    ['Skipped:', $info['rows_skipped'],],
                ]
            );

            if (!empty($info['skipped_rows_content'])) {
                $io->text('Skipped rows:');
                $io->table(
                    ['ProductCode', 'ProductName', 'ProductDescription', 'Stock', 'Cost', 'Discontinued'],
                    $info['skipped_rows_content']
                );
            }
        } catch (Exception $exc) {
            $io->error('ERROR#'.$exc->getCode().': '.$exc->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
