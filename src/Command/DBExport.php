<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\ExportImportTools\Command;

use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use OxidSolutionCatalysts\ExportImportTools\Traits\CommonMethods;
use OxidSolutionCatalysts\ExportImportTools\Traits\PdoMethods;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBExport extends Command
{
    use CommonMethods;
    use PdoMethods;

    protected static $defaultName = 'osc:db:export';

    protected function configure(): void
    {
        $this->setDescription(
            'Export Database to /export folder.
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

        $this->export(
            $this->getStringConfigParam('dbHost'),
            $this->getStringConfigParam('dbName'),
            $this->getStringConfigParam('dbUser'),
            $this->getStringConfigParam('dbPwd'),
            $this->getExportPath() . $cliRunConfig[$this->confKeyDump],
            $cliRunConfig[$this->confKeyTable],
            $cliRunConfig[$this->confKeyAnonymize],
            $this->getStringConfigParam('dbPort')
        );

        return 0;
    }

    protected function getExportPath(): string
    {
        return $this->getRealPath("export" . DIRECTORY_SEPARATOR, true);
    }

    protected function export(
        string $host,
        string $dbName,
        string $userName,
        string $passWd,
        string $dumpFile,
        array $onlyTables = [],
        array $anonymizeTables = [],
        string $port = ''
    ): void {
        try {
            $pdoDsnConnection = $this->getPdoDsnConnection($host, $dbName, $port);

            $dump = new Mysqldump(
                $pdoDsnConnection,
                $userName,
                $passWd,
                [
                    'add-drop-table' => true,
                    'skip-definer' => true,
                    'skip-triggers' => true,
                    'include-tables' => $onlyTables
                ]
            );

            // anonymize if wanted
            if (count($anonymizeTables)) {
                $dump->setTransformTableRowHook(function ($tableName, array $row) use ($anonymizeTables) {
                    foreach ($anonymizeTables as $anonymizeTable => $anonymizeRows) {
                        if ($tableName === $anonymizeTable) {
                            foreach ($anonymizeRows as $anonymizeRow) {
                                $row[$anonymizeRow] = (string) random_int(1000000, 9999999);
                            }
                        }
                    }
                    return $row;
                });
            }

            $dump->start($dumpFile);

            $this->output->writeLn(sprintf(
                "<comment>Dump completed in %s</comment>",
                $dumpFile
            ));
        } catch (Exception $e) {
            $this->output->writeLn(sprintf(
                "<comment>mysqldump-php error: `%s`</comment>",
                $e->getMessage()
            ));
        }
    }
}
