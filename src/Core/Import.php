<?php

namespace OxidSolutionCatalysts\CliExportImport\Core;

use OxidSolutionCatalysts\CliExportImport\Traits\PdoMethods;
use PDO;
use PDOException;
use Exception;
use Error;
use RuntimeException;

/**
 * PDO class to import sql from a .sql file
 * adapted from https://github.com/dcblogdev/sql-import
 */
class Import
{
    use PdoMethods;

    private PDO $pdoDb;
    private string $dumpFile;
    private string $userName;
    private string $passWd;
    private string $dbName;
    private string $host;
    private string $port;

    /**
     * Connect to the dbName
     * @throws RuntimeException
     */
    public function __construct(
        string $dumpFile,
        string $userName,
        string $passWd,
        string $dbName,
        string $host,
        string $port = ''
    ) {
        $this->dumpFile = $dumpFile;
        $this->userName = $userName;
        $this->passWd = $passWd;
        $this->dbName = $dbName;
        $this->host = $host;
        $this->port = $port;

        $this->connect();

        $this->importFile();
    }

    /**
     * @throws RuntimeException
     */
    private function connect(): void
    {
        try {
            $pdoDsnConnection = $this->getPdoDsnConnection($this->host, $this->dbName, $this->port);
            $this->pdoDb = new PDO(
                $pdoDsnConnection,
                $this->userName,
                $this->passWd
            );
            $this->pdoDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf(
                "Cannot connect: %s",
                $e->getMessage()
            ));
        }
    }

    /**
     * @throws RuntimeException
     */
    private function query(string $query): void
    {
        try {
            $this->pdoDb->query($query);
        } catch (Error $e) {
            throw new RuntimeException(sprintf(
                "Error with query: %s",
                $e->getMessage()
            ));
        }
    }

    /**
     * @throws RuntimeException
     */
    private function importFile(): void
    {
        try {
            if (!file_exists($this->dumpFile)) {
                throw new RuntimeException(sprintf(
                    "Error: File not found: %s",
                    $this->dumpFile
                ));
            }

            $fileHandle = fopen($this->dumpFile, 'rb');

            if ($fileHandle) {
                // Temporary variable, used to store current query
                $tmpLine = '';

                // Loop through each line
                while (($line = fgets($fileHandle)) !== false) {
                    // Skip it if it's a comment
                    if ($line === '' || strpos($line, '--') === 0) {
                        continue;
                    }

                    // Add this line to the current segment
                    $tmpLine .= $line;

                    // If it has a semicolon at the end, it's the end of the query
                    if (substr(trim($line), -1, 1) === ';') {
                        $this->query($tmpLine);

                        // Reset temp variable to empty
                        $tmpLine = '';
                    }
                }
                fclose($fileHandle);
            }
        } catch (Exception $e) {
            throw new RuntimeException(sprintf(
                "Error importing: %s",
                $this->dumpFile
            ));
        }
    }
}
