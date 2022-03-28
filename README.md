<h1 align="center">Migrated Data Testing Tool (MDTT)</h1>
<p align="center">[![Tests](https://github.com/axelerant/mdtt/actions/workflows/tests.yml/badge.svg)](https://github.com/axelerant/mdtt/actions/workflows/tests.yml)</p>

This tools helps you to verify the quality of your migrated data.

## Installation

```shell
composer require --dev axelerant/mdtt
```

## Usage

Basically you follow these steps:

1. Specify test specifications
1. Specify test definitions
1. Run the tests

### Test specification

Specify the test specification inside the directory `tests/mdtt`. The specification must be written inside the `spec.php`. Below is a sample specification:

```php
<?php

return [
    'databases' => [
        // Source database credentials
        'source_db' => [
            'database' => "db_drupal7",
            'username' => "db",
            'password' => "db",
            'host' => "127.0.0.1",
            'port' => "59002",
        ],
        // Destination database credentials
        "destination_db" => [
            'database' => "db",
            'username' => "db",
            'password' => "db",
            'host' => "127.0.0.1",
            'port' => "59002",
        ],
    ]
];
```

### Test definitions

The test definitions are written in `yaml` format, and must be present inside the directory `tests/mdtt`. Let's say that you want to validate the migrated user data. Create a file called `validate_users.yml`. The name of the file doesn't matter.

#### Database

```yml
# Test definition ID
id: validate_users
# Test definition description
description: Validates users
# Group to which this test belongs to
group: migration_validation
# The query used for generating the source dataset
source:
  type: query
  data: "select * from users"
  # The source database credentials
  database: source_db
# The query used for generating the destination dataset
destination:
  type: query
  data: "select * from users_field_data"
  # The destination database credentials
  database: destination_db
# Test cases
tests:
  -
    # Field from source datasource used in this comparison
    sourceField: name
    # Field from destination datasource used in this comparison
    destinationField: name
  -
    sourceField: mail
    destinationField: mail
```

You can find the basic template for the tool usage [here](https://github.com/axelerant/mdtt-usage).

## Run tests

```shell
./vendor/bin/mdtt run -vvv
```

## Features

## Supported types of source

- Database (MySQL)

## Supported types of destination

- Database (MySQL)

## Contribution

Fork this repository and do the changes.

### Run tests

```shell
composer test
```
