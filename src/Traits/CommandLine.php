<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\CliExportImport\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
* trait CommandLine
*
*/
trait CommandLine
{
    /**
     * OutputInterface instance
     */
    protected ?OutputInterface $output = null;

    /**
     * InputInterface instance
     */
    protected ?InputInterface $input = null;

    protected function getOptionYaml(): string
    {
        $sEnvironment = '';
        if ($this->input->hasOption('yaml')) {
            $sEnvironment = $this->input->getOption('yaml');
        }
        return $sEnvironment;
    }
}
