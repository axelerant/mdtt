<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

use Mdtt\DataSource\DataSource;
use Mdtt\Exception\SetupException;
use Mdtt\Utility\DataSource\Json as JsonDataSourceUtility;

class Validator
{
    private JsonDataSourceUtility $jsonDataSourceUtility;

    public function __construct(JsonDataSourceUtility $jsonDataSourceUtility)
    {
        $this->jsonDataSourceUtility = $jsonDataSourceUtility;
    }

    /**
     * Validates whether the datasource information in test definition is valid.
     *
     * @param string $type
     * @param array<string> $rawDataSourceDefinition
     *
     * @return \Mdtt\DataSource\DataSource
     * @throws \Mdtt\Exception\SetupException
     */
    public function validate(string $type, array $rawDataSourceDefinition): DataSource
    {
        if (!in_array($type, ["source", "destination"])) {
            throw new SetupException("Incorrect data source type is passed.");
        }

        /** @var string $dataSourceType */
        $dataSourceType = $rawDataSourceDefinition['type'];
        if ($dataSourceType === "database") {
            $this->doValidateDatabase($rawDataSourceDefinition);

            if ($type === "source") {
                return new \Mdtt\Source\Database(
                    $rawDataSourceDefinition['data'],
                    $rawDataSourceDefinition['database']
                );
            }

            if ($type === "destination") {
                return new \Mdtt\Destination\Database(
                    $rawDataSourceDefinition['data'],
                    $rawDataSourceDefinition['database']
                );
            }
        }

        if ($dataSourceType === "json") {
            $this->doValidateJson($rawDataSourceDefinition);

            if (isset($rawDataSourceDefinition['auth_basic'])) {
                $this->jsonDataSourceUtility->setAuthBasicCredential($rawDataSourceDefinition['auth_basic']);
            }

            if ($type === "source") {
                return new \Mdtt\Source\Json(
                    $rawDataSourceDefinition['data'],
                    $rawDataSourceDefinition['selector'],
                    $this->jsonDataSourceUtility,
                );
            }

            if ($type === "destination") {
                return new \Mdtt\Destination\Json(
                    $rawDataSourceDefinition['data'],
                    $rawDataSourceDefinition['selector'],
                    $this->jsonDataSourceUtility
                );
            }
        }

        throw new SetupException(sprintf("Unexpected data source type %s and data source definition passed.", $type));
    }

    /**
     * @param array<string> $rawDataSourceDefinition
     *
     * @return void
     */
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

    /**
     * @param array<string> $rawDataSourceDefinition
     *
     * @return void
     */
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
