<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\CliExportImport\Command;

use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\CliExportImport\Traits\CommonMethods;
use OxidSolutionCatalysts\CliExportImport\Traits\YamlConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBExport extends Command
{
    use YamlConfig;
    use CommonMethods;

    protected static $defaultName = 'osc:db:export';

    protected function configure(): void
    {
        $this->setDescription('Export Database to /export folder. Optional control via yaml-file in var/configuration/cliexportimport/')
            ->addOption(
                '--yaml',
                '',
                InputOption::VALUE_OPTIONAL,
                'Name of yaml-file in in config-folder var/configuration/cliexportimport/'
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
        $config = Registry::getConfig();

        $cliRunConfig = $this->getYamlConfig(
            $this->getOptionYaml()
        );

        $this->export(
            $config->getConfigParam('dbHost'),
            $config->getConfigParam('dbName'),
            $config->getConfigParam('dbUser'),
            $config->getConfigParam('dbPwd'),
            $this->getExportPath() . $cliRunConfig['dumpFileName'],
            $cliRunConfig['onlyTables'],
            $cliRunConfig['anonymizeRowsInTables'],
            $config->getConfigParam('dbPort')
        );

        return 0;
    }

    protected function getExportPath(): string
    {
        return $this->getRealPath("export" . DIRECTORY_SEPARATOR);
    }

    protected function export(
        string $host,
        string $dbName,
        string $userName,
        string $passWd,
        string $dumpFile,
        array $onlyTables = [],
        array $anonymizeRowsInTables = [],
        string $port = ''
    ): void
    {
        try {
            $pdoDsnConnection = 'mysql:host=' . $host
                . ($port ? ';port=' . $port : '')
                . ';dbname=' . $dbName;

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
            if (count($anonymizeRowsInTables)) {
                $dump->setTransformTableRowHook(function ($tableName, array $row) use ($anonymizeRowsInTables) {
                    foreach ($anonymizeRowsInTables as $anonymizeTable => $anonymizeRows) {
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

        } catch (Exception $e) {
            $this->output->writeLn(sprintf(
                "<comment>mysqldump-php error: `%s`</comment>",
                $e->getMessage()
            ));
        }
    }
}