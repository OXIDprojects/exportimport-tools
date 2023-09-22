# OE-Console Im- and Export

Extension for OXID OE-Console to Im- and Export the Database via commandline

## Documentation

this file

## Branch Compatibility

* b-6.5.x branch is compatible with OXID eShop compilation 6.3, 6.4, 6.5

## Installation

Installation via composer
```
composer require oxid-solution-catalysts/cliexportimport ^1.0.0
```

## Usage

### Configuration

Use a configuration YAML e.g. dump.yaml and save it here: var/configuration/cliexportimport/dump.yaml

Contents of the configuration YAML
```
dumpFileName: dump.sql
onlyTables:
  - oxarticles
  - oxartextends
anonymizeRowsInTables:
  oxuser:
    - oxfname
    - oxlname
  oxorder:
    - oxbillfname
    - oxbilllname
    - oxdelfname
    - oxdellname
```
* "dumpFileName": Name of the dumpfile
* "onlyTables": export only the tables you want. Leave emtpy, if you want dump all tables
* "anonymizeRowsInTables": possibility to anonymize Data. This is good, if you want use the database in test-environments. Leave emtpy, if you want dont want anonymize Data.

### Dump or export Data
```
vendor/bin/oe-console osc:db:export --yaml=dump.yaml
```
The dump is created in the /source/export/ directory. The name of the dump is the defined "dumpFileName" from the Configuration YAML.

The --yaml option contains the name of the configuration file, which is located in the directory: var/configuration/cliexportimport/ 

### Import Data
```
vendor/bin/oe-console osc:db:import --yaml=dump.yaml
```
The dump is read from the /source/import/ directory. The name of the dump is the defined "dumpFileName" from the Configuration YAML

The --yaml option contains the name of the configuration file, which is located in the directory: var/configuration/cliexportimport/

## Running tests

### Run

Code Style
```
composer phpcs --working-dir=extensions/osc/cliexportimport
```

PHPmd
```
composer phpmd --working-dir=extensions/osc/cliexportimport
```

PHPStan
```
composer phpstan --working-dir=extensions/osc/cliexportimport
```


