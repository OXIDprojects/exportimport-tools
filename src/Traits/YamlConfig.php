<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\CliExportImport\Traits;

use OxidEsales\Facts\Facts;
use Symfony\Component\Yaml\Yaml;

/**
* trait YamlConfig
*
*/
trait YamlConfig
{
    protected function getYamlConfigPath(): string
    {
        $DS = DIRECTORY_SEPARATOR;
        $facts = new Facts();
        $base = realpath($facts->getShopRootPath()) . $DS . "var" . $DS . "configuration" . $DS. "cliexportimport" . $DS;
        $this->checkPath($base);
        return $base;
    }

    protected function checkPath($path): void
    {
        if ((file_exists($path) === false) && !mkdir($path, 0755, true) && !is_dir($path)) {
            $this->output->writeLn(sprintf('<comment>Directory "%s" was not created</comment>', $path));
        }
    }

    protected function getYamlConfig(string $yamlFileName = ''): array
    {
        $cliRunConfig = [];

        $yamlFile = $this->getYamlConfigFile($yamlFileName);

        if ($yamlFile) {
            // now try to read YAML
            $yaml = $this->getYamlStringFromFile($yamlFile);
            $cliRunConfig = Yaml::parse($yaml);
        } else {
            $this->output->writeLn("<comment>No config passed, use default.</comment>");
        }

        if ($yamlFile && !is_array($cliRunConfig)) {
            $cliRunConfig = [];
            $this->output->writeLn(sprintf(
                "<comment>No valid YAML data found: `%s`</comment>",
                realpath($yamlFile)
            ));
        }

        return $cliRunConfig;
    }

    protected function getYamlConfigFile(string $yamlFileName = ''): string
    {
        $yamlFile = $this->getYamlConfigPath() .$yamlFileName;
        return file_exists($yamlFile) ? $yamlFile : '';
    }

    protected function getYamlStringFromFile(string $yamlFile): string
    {
        return $yamlFile ? file_get_contents($yamlFile) : '';
    }
}
