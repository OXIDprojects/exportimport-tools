<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\CliExportImport\Command;

use OxidSolutionCatalysts\CliExportImport\Traits\CommandLine;
use OxidSolutionCatalysts\CliExportImport\Traits\YamlConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBExport extends Command
{
    use YamlConfig;
    use CommandLine;

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

        $app = $this->getApplication();

        $cliRunConfig = $this->getYamlConfig(
            $this->getOptionYaml()
        );

        return 0;
    }
}