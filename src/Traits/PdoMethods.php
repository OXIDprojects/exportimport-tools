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
* trait PdoMethods
*
*/
trait PdoMethods
{
    protected function getPdoDsnConnection(string $host, string $dbName, string $port = ''): string
    {
        return 'mysql:host=' . $host
            . ($port ? ';port=' . $port : '')
            . ';dbname=' . $dbName;
    }
}
