<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\ExportImportTools\Command;

use OxidSolutionCatalysts\ExportImportTools\Traits\CommonMethods;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OxidSolutionCatalysts\ExportImportTools\Core\Import;

class DBImport extends Command
{
    use CommonMethods;

    protected static $defaultName = 'osc:db:import';

    protected function configure(): void
    {
        $this->setDescription(
            'Import Database from /import folder.
             Optional control via yaml-file in var/configuration/exportimport-tools/'
        )->addOption(
            '--yaml',
            '',
            InputOption::VALUE_OPTIONAL,
            'Name of yaml-file in in config-folder var/configuration/exportimport-tools/'
        );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $cliRunConfig = $this->getYamlConfig(
            $this->getOptionYaml()
        );

        $this->import(
            $this->getStringConfigParam('dbHost'),
            $this->getStringConfigParam('dbName'),
            $this->getStringConfigParam('dbUser'),
            $this->getStringConfigParam('dbPwd'),
            $this->getImportPath() . $cliRunConfig[$this->confKeyDump],
            $this->getStringConfigParam('dbPort')
        );

        return 0;
    }

    protected function import(
        string $host,
        string $dbName,
        string $userName,
        string $passWd,
        string $dumpFile,
        string $port = ''
    ): void {
        try {
            new Import(
                $dumpFile,
                $userName,
                $passWd,
                $dbName,
                $host,
                $port
            );

            $this->output->writeLn(sprintf(
                "<comment>Import completed from %s</comment>",
                $dumpFile
            ));
        } catch (RuntimeException $e) {
            $this->output->writeLn(sprintf(
                "<comment>mysql-import-php error: `%s`</comment>",
                $e->getMessage()
            ));
        }
    }

    protected function getImportPath(): string
    {
        return $this->getRealPath("import" . DIRECTORY_SEPARATOR, true);
    }
}
