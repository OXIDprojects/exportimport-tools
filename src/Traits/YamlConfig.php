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
    use CommonMethods;

    protected function getYamlConfigPath(): string
    {
        $DS = DIRECTORY_SEPARATOR;
        return $this->getRealPath("var" . $DS . "configuration" . $DS. "cliexportimport" . $DS);
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
            $this->output->writeLn(sprintf(
                "<comment>No config passed, use default. (create a %s as example)</comment>",
                $this->setExampleYaml()
            ));
        }

        if ($yamlFile && !is_array($cliRunConfig)) {
            $cliRunConfig = [];
            $this->output->writeLn(sprintf(
                "<comment>No valid YAML data found: `%s`</comment>",
                realpath($yamlFile)
            ));
        }

        // fix missing config-elements and return
        return array_merge($this->getConfigDefault(), $cliRunConfig);
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

    protected function setExampleYaml(): string
    {
        $yamlFile = 'example.yaml';
        $this->setDataToYamlFile(
            $this->getYamlConfigPath() . $yamlFile,
            $this->getConfigDefault()
        );
        return $yamlFile;
    }

    protected function setDataToYamlFile(string $yamlFile, array $data = []): void
    {
        if ($yamlFile && $sYaml = $this->getYamlFromArray($data)) {
            file_put_contents(
                $yamlFile,
                $sYaml
            );
        }
    }

    protected function getYamlFromArray(array $data = []): string
    {
        return Yaml::dump($data, 5, 2);
    }

    protected function getConfigDefault(): array
    {
        return [
            'dumpFileName' => 'dump.sql',
            'onlyTables' => [],
            'anonymizeRowsInTables' => [
                'tablename' => [
                    'colname1', 'colname2'
                ]
            ]
        ];
    }
}
