<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

use Mdtt\DataSource;
use Mdtt\Exception\SetupException;

class Validator
{
    /**
     * Validates whether the datasource information in test definition is valid.
     *
     * @param string $type
     * @param array<string> $rawDataSourceDefinition
     *
     * @return \Mdtt\DataSource
     */
    public function validate(string $type, array $rawDataSourceDefinition): DataSource
    {
        if (!in_array($type, ["source", "destination"])) {
            throw new SetupException("Incorrect data source type is passed.");
        }

        if ($type === "source" && $rawDataSourceDefinition['type'] === "database") {
            $this->doValidateDatabase($rawDataSourceDefinition);
            return new \Mdtt\Source\Database($rawDataSourceDefinition['data'], $rawDataSourceDefinition['database']);
        }

        if ($type === "destination" && $rawDataSourceDefinition['type'] === "database") {
            $this->doValidateDatabase($rawDataSourceDefinition);
            return new \Mdtt\Destination\Database(
                $rawDataSourceDefinition['data'],
                $rawDataSourceDefinition['database']
            );
        }

        throw new SetupException(sprintf("Unexpected data source type %s and data source definition passed.", $type));
    }

    private function doValidateDatabase(array $rawDataSourceDefinition): void
    {
        $dbValidator = new Database();
        $isValid = $dbValidator->validate($rawDataSourceDefinition);
        if (!$isValid) {
            throw new SetupException(
                sprintf("All information are not passed for %s", $rawDataSourceDefinition['type'])
            );
        }
    }

    private function doValidateJson(array $rawDataSourceDefinition): void
    {
        $jsonValidator = new Json();
        $isValid = $jsonValidator->validate($rawDataSourceDefinition);
        if (!$isValid) {
            throw new SetupException(
                sprintf(
                    "All information are not passed for %s",
                    $rawDataSourceDefinition['type']
                )
            );
        }
    }
}
