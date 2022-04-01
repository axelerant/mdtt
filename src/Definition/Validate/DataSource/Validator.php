<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

use Mdtt\DataSource;
use Mdtt\Exception\SetupException;
use Mdtt\Utility\HttpClient;

class Validator
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Validates whether the datasource information in test definition is valid.
     *
     * @param string $type
     * @param array<string> $rawDataSourceDefinition
     *
     * @return \Mdtt\DataSource
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

        if ($type === "json") {
            $this->doValidateJson($rawDataSourceDefinition);

            if ($dataSourceType === "source") {
                return new \Mdtt\Source\Json(
                    $rawDataSourceDefinition['data'],
                    $rawDataSourceDefinition['selector'],
                    $this->httpClient,
                    0,
                    $rawDataSourceDefinition['auth_basic'] ?? null
                );
            }

            if ($dataSourceType === "destination") {
                return new \Mdtt\Destination\Json(
                    $rawDataSourceDefinition['data'],
                    $rawDataSourceDefinition['selector'],
                    $this->httpClient,
                    0,
                    $rawDataSourceDefinition['auth_basic'] ?? null
                );
            }
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
