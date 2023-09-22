<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\CliExportImport\Traits;

use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\Yaml\Yaml;
use OxidEsales\Facts\Facts;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
* trait CommandLine
*
*/
trait CommonMethods
{
    protected string $confKeyDump = 'dumpFileName';
    protected string $confKeyTable = 'onlyTables';
    protected string $confKeyAnonymize = 'anonymizeRowsInTables';
    /**
     * OutputInterface instance
     */
    protected OutputInterface $output;

    /**
     * InputInterface instance
     */
    protected InputInterface $input;

    protected function getRealPath(string $path, bool $sourcePath = false): string
    {
        $facts = new Facts();
        $root = $sourcePath ? $facts->getSourcePath() : $facts->getShopRootPath();
        $base = realpath($root) . DIRECTORY_SEPARATOR . $path;
        $this->checkPath($base);
        return $base;
    }

    protected function checkPath(string $path): void
    {
        if (
            (file_exists($path) === false) &&
            !mkdir($path, 0755, true) &&
            !is_dir($path)
        ) {
            $this->output->writeLn(sprintf(
                '<comment>Directory "%s" was not created</comment>',
                $path
            ));
        }
    }

    protected function getOptionYaml(): string
    {
        $result = '';
        if ($this->input->hasOption('yaml')) {
            $result = $this->input->getOption('yaml');
            $result = is_string($result) ? $result : '';
        }
        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getStringConfigParam(string $var = ''): string
    {
        $config = Registry::getConfig();
        $result = $config->getConfigParam($var);
        return is_string($result) ? $result : '';
    }
    protected function getYamlConfigPath(): string
    {
        return $this->getRealPath(
            "var" . DIRECTORY_SEPARATOR .
            "configuration" . DIRECTORY_SEPARATOR .
            "cliexportimport" . DIRECTORY_SEPARATOR
        );
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function getYamlConfig(string $yamlFileName = ''): array
    {
        $cliRunConfig = [];

        $yamlFile = $this->getYamlConfigFile($yamlFileName);
        if ($yamlFile) {
            // now try to read YAML
            $this->output->writeLn(sprintf(
                "<comment>Use config: `%s`</comment>",
                realpath($yamlFile)
            ));
            $yaml = $this->getYamlStringFromFile($yamlFile);
            $cliRunConfig = (array)Yaml::parse($yaml);
        } else {
            $this->output->writeLn(
                "<comment>No configuration passed, default used.</comment>",
            );
            $this->setExampleYaml();
        }

        if ($yamlFile && !count($cliRunConfig)) {
            $cliRunConfig = [];
            $this->output->writeLn(sprintf(
                "<comment>No valid YAML data found: `%s`</comment>",
                realpath($yamlFile)
            ));
        }

        // bulletproof config
        $defaultConfig = $this->getConfigDefault();
        $cliRunConfig[$this->confKeyDump] = is_string($cliRunConfig[$this->confKeyDump]) && !empty($cliRunConfig[$this->confKeyDump]) ?
            $cliRunConfig[$this->confKeyDump] :
            $defaultConfig[$this->confKeyDump];
        $cliRunConfig[$this->confKeyTable] = is_array($cliRunConfig[$this->confKeyTable]) ?
            $cliRunConfig[$this->confKeyTable] :
            [];
        $cliRunConfig[$this->confKeyAnonymize] = is_array($cliRunConfig[$this->confKeyAnonymize]) ?
            $cliRunConfig[$this->confKeyAnonymize] :
            [];

        return $cliRunConfig;
    }

    protected function getYamlConfigFile(string $yamlFileName = ''): string
    {
        $yamlFile = $this->getYamlConfigPath() . $yamlFileName;
        return ($yamlFileName && file_exists($yamlFile)) ? $yamlFile : '';
    }

    protected function getYamlStringFromFile(string $yamlFile): string
    {
        $result = '';
        if ($yamlFile) {
            $result = file_get_contents($yamlFile);
            $result = $result !== false ? $result : '';
        }
        return $result;
    }

    protected function setExampleYaml(): string
    {
        $yamlFile = 'example.yaml';
        if (!$this->getYamlConfigFile($yamlFile)) {
            $yamlFilePath = $this->getYamlConfigPath() . $yamlFile;
            $this->setDataToYamlFile(
                $yamlFilePath,
                $this->getConfigDefault()
            );
            $this->output->writeLn(sprintf(
                "<comment>Create a %s as example. You can use this file with parameter --yaml=%s</comment>",
                realpath($yamlFilePath),
                $yamlFile
            ));
        }
        return $yamlFile;
    }

    protected function setDataToYamlFile(string $yamlFile, array $data = []): void
    {
        $sYaml = $this->getYamlFromArray($data);
        if ($yamlFile && $sYaml) {
            file_put_contents(
                $yamlFile,
                $sYaml
            );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getYamlFromArray(array $data = []): string
    {
        return Yaml::dump($data, 5, 2);
    }

    protected function getConfigDefault(): array
    {
        return [
            $this->confKeyDump => 'dump.sql',
            $this->confKeyTable => [],
            $this->confKeyAnonymize => [
                'oxuser' => [
                    'oxfname', 'oxlname'
                ]
            ]
        ];
    }
}
