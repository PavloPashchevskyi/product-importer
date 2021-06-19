<?php

namespace App\Command;

use App\Service\ProductImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

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
                InputOption::VALUE_OPTIONAL,
                'Reads data from CSV-file, but does not store in database table',
                false
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
        $output->writeln('Import from CSV-file has been started');
        try {
            $filePath = $input->getOption('filepath');
            $startTime = microtime(true);
            $info = $this->productImportService->importFromCSV($filePath);
            if ($input->getOption('test') === false) {
                $this->productImportService->storeToDatabase($info['filtered_rows']);
            }
            $endTime = microtime(true);
            $execTime = $endTime - $startTime;
            $output->writeln('Import has been finished! Executed for '.$execTime.' sec.');

            $output->writeln('Total rows quantity in CSV file: '.$info['total_rows_qty']);
            $output->writeln(' Of which');
            $output->writeln('    imported successfully: '.$info['rows_successfully_imported']);
            $output->writeln('    skipped: '.$info['rows_skipped']);

            $output->writeln('Skipped rows:');
            $strSkippedRows = "ProductCode | ProductName | ProductDescription | Stock | Cost | Discontinued\n";
            $strSkippedRows .= "----------------------------------------------------------------------------\n";
            foreach ($info['skipped_rows_content'] as $rowSkipped) {
                foreach ($rowSkipped as $j => $columnSkipped) {
                    $strSkippedRows .= $columnSkipped;
                    $strSkippedRows .= ($j < count($rowSkipped) - 1) ? '       | ' : '';
                }
                $strSkippedRows .= "\n";
            }
            $output->writeln($strSkippedRows);

        } catch (Exception $exc) {
            $output->writeln('ERROR#'.$exc->getCode().': '.$exc->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
