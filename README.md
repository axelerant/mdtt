<h1 align="center">Migrated Data Testing Tool (MDTT)</h1>

[![Tests](https://github.com/axelerant/mdtt/actions/workflows/tests.yml/badge.svg)](https://github.com/axelerant/mdtt/actions/workflows/tests.yml)

This tools helps you to verify the quality of your migrated data.

## Installation

```shell
composer require --dev axelerant/mdtt
```

## Usage

Basically you follow these steps:

1. Specify test specifications
2. Specify test definitions
3. _Optionally_, specify transform plugins
4. Run the tests

You can find the basic template for the tool usage [here](https://github.com/axelerant/mdtt-usage).

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
    ],
    'http' => [
        // JSON datasource basic authentication credetials
        'source' => [
            'username' => 'username',
            'password' => 'fubar',
            // Basic authentication protocol: basic, digest, or ntlm.
            // Protocol is optional. If not mentioned, basic is considered.
            'protocol' => 'ntlm',
        ],
        'destination' => [
            'username' => 'username',
            'password' => 'puffball',
        ]
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
  type: database
  data: "select * from users"
  # The source database credentials
  database: source_db
# The query used for generating the destination dataset
destination:
  type: database
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

#### JSON

Consider that the data is exposed by the _source_ legacy system in the following format:

```json
{
      "jsonapi": {
        "version": 1.2,
      },
      "results": [
        {
          "employee": {
              "id": 3453,
              "info": {
                "name": "Aloy",
                "email": "aloy@nora.tribe",
                "gender": "female",
                "status": "active"
              }
          }
        },
        {
          "employee": {
              "id": 3452,
              "info": {
                "name": "Varl",
                "email": "varl@nora.tribe",
                "gender": "male",
                "status": "active"
              }
          }
        },
        {
          "employee": {
              "id": 3450,
              "info": {
                "name": "Rost",
                "email": "rost@nora.tribe",
                "gender": "male",
                "status": "inactive"
              }
          }
        }
      ]
    }
```

Consider that the _destination_ new system exposes data in the following format:

```json
      "jsonapi": {
        "version": 1.2,
      },
      "results": [
        {
            "id": 3453,
            "name": "Aloy",
            "email": "aloy@nora.tribe",
            "gender": "female",
            "status": "active"
        },
        {
            "id": 3452,
            "name": "Varl",
            "email": "varl@nora.tribe",
            "gender": "male",
            "status": "active"
        },
        {
            "id": 3450,
            "name": "Rost",
            "email": "rost@nora.tribe",
            "gender": "male",
            "status": "active"
        }
      ]
    }
```

```yaml
id: validate_public_apis
description: Validate public apis
group: migration_validation
# The endpoint that returns the source dataset.
source:
  type: json
  data: https://dev.legacy-system.com/api/v1/users
  # The pointer where the list of items resides. Refer https://github.com/halaxa/json-machine#what-is-json-pointer-anyway for examples
  selector: "/results"
  # Datasource basic authentication credentials. Note that the key is same as mentioned in the test specification. This is optional if the endpoint is publicly accessible.
  credential: source
# The endpoint that returns the destination dataset.
destination:
  type: json
  data: https://dev.new-system.com/api/v1/users
  selector: "/results"
  credential: destination
tests:
  -
    # `/` separated field which you want to test.
    # This is applicable when the value you want to test is in nested format.
    sourceField: "employee/info/name"
    destinationField: name
  -
    sourceField: "employee/info/email"
    destinationField: email
```

### Transform plugin

There could be a scenario where instead of directly storing the data from source, it must be transformed in some way (say, whitespaces must be stripped) before storing it in the destination database. A QA engineer can write their own plugin, to validate the business logic that does the transformation.

The test case would look like this:

```yaml
tests:
  -
    sourceField: name
    transform: trim
    destinationField: name
```

The QA engineer must specify the plugin class inside `tests/mdtt/src/Plugin/Transform`. The file name (and, class name) must be same as the plugin name mentioned in the test case with the first character in upper case, i.e. `Trim`. The plugin class must implement `\Mdtt\Transform\Transform` interface.

```php
<?php

// File location: tests/mdtt/src/Plugin/Transform/Trim.php.

class Trim implements \Mdtt\Transform\Transform
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        // Specifies the plugin name.
        // This must be unique, if you have multiple plugin definitions.
        // The same plugin name must be mentioned in the test case.
        return "trim";
    }

    /**
     * @inheritDoc
     */
    public function process(mixed $data): mixed
    {
        // The logic that does the transformation.
        return trim($data);
    }
}

```

## Run tests

```shell
./vendor/bin/mdtt run
```

### Verbose mode

```shell
./vendor/bin/mdtt run -vvv
```

You can view all available options by doing this:

```shell
./vendor/bin/mdtt run --help
```

### Specify email where notification will be sent when test completes

```shell
./vendor/bin/mdtt run --email foo@bar.mail
```

### Logs

By default, the log files are created inside directory called `logs`. The directory will be present in the root of the test suite. The log files are named based on the datetime when the test is executed.

## Features

## Supported types of source

- Database (MySQL)
- JSON

## Supported types of destination

- Database (MySQL)
- JSON

## Supported types of channels to notify when test completes

- Email

## Contribution

Fork this repository and do the changes.

### Run tests

```shell
composer test
```
