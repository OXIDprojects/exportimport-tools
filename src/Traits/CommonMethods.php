<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\CliExportImport\Traits;

use OxidEsales\Facts\Facts;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
* trait CommandLine
*
*/
trait CommonMethods
{
    /**
     * OutputInterface instance
     */
    protected ?OutputInterface $output = null;

    /**
     * InputInterface instance
     */
    protected ?InputInterface $input = null;

    protected function getRealPath($path): string
    {
        $facts = new Facts();
        $base = realpath($facts->getShopRootPath()) . DIRECTORY_SEPARATOR . $path;
        $this->checkPath($base);
        return $base;
    }

    protected function checkPath($path): void
    {
        if ((file_exists($path) === false) && !mkdir($path, 0755, true) && !is_dir($path)) {
            $this->output->writeLn(sprintf('<comment>Directory "%s" was not created</comment>', $path));
        }
    }

    protected function getOptionYaml(): string
    {
        $sEnvironment = '';
        if ($this->input->hasOption('yaml')) {
            $sEnvironment = $this->input->getOption('yaml');
        }
        return $sEnvironment;
    }
}
