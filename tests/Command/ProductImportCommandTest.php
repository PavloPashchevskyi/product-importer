<?php
declare(strict_types=1);

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ProductImportCommandTest extends KernelTestCase
{
    private const TEST_FILE_PATH = '/tests/uploads/stock.csv';

    public function testTestExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:product:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--filepath' => $kernel->getProjectDir().self::TEST_FILE_PATH,
            '--test' => true,
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('CSV-file metadata', $output);
    }

    public function testProdExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        // truncate table
        $command = $application->find('dbal:run-sql');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'sql' => 'TRUNCATE TABLE `tblproductdata`;',
            '--env'=> 'test',
        ]);
        // end truncate table

        $command = $application->find('app:product:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--filepath' => $kernel->getProjectDir().self::TEST_FILE_PATH,
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Add imported data to database? [yes]:', $output);
        $input = $commandTester->setInputs(['yes']);
        $this->assertStringContainsString('CSV-file metadata', $output);
    }
}
