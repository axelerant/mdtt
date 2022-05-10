<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

use Mdtt\DataSource\DataSource;
use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\Load;
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
     * @param string $specLocation
     *
     * @return \Mdtt\DataSource\DataSource
     * @throws \Mdtt\Exception\SetupException
     */
    public function validate(
        string $type,
        array $rawDataSourceDefinition,
        string $specLocation = Load::DEFAULT_SPEC_LOCATION
    ): DataSource {
        if (!in_array($type, ["source", "destination"])) {
            throw new SetupException("Incorrect data source type is passed.");
        }

        $specification = require $specLocation;

        /** @var string $dataSourceType */
        $dataSourceType = $rawDataSourceDefinition['type'];
        if ($dataSourceType === "database") {
            $this->doValidateDatabase($rawDataSourceDefinition, $specification['databases']);

            $databaseName = $rawDataSourceDefinition['database'];
            /** @var array<string, array<string, string>> $databaseSpecification */
            $databaseSpecification = $specification['databases'];

            return new \Mdtt\DataSource\Database(
                $rawDataSourceDefinition['data'],
                $databaseSpecification[$databaseName]['database'],
                $databaseSpecification[$databaseName]['username'],
                $databaseSpecification[$databaseName]['password'],
                $databaseSpecification[$databaseName]['host'],
                (int) $databaseSpecification[$databaseName]['port']
            );
        }

        if ($dataSourceType === "json") {
            $this->doValidateJson($rawDataSourceDefinition);
            $username = null;
            $password = null;
            $protocol = null;

            if (isset($rawDataSourceDefinition['credential'])) {
                $specification = require $specLocation;

                $httpSpecification = $specification['http'];
                /** @var string $credentialKey */
                $credentialKey = $rawDataSourceDefinition['credential'];

                if (!isset(
                    $httpSpecification[$credentialKey]['username'],
                    $httpSpecification[$credentialKey]['password']
                )) {
                    throw new SetupException(
                        "Basic auth username and password are not provided, but credential is specified."
                    );
                }

                $username = $httpSpecification[$credentialKey]['username'];
                $password = $httpSpecification[$credentialKey]['password'];
                $protocol = $httpSpecification[$credentialKey]['protocol'] ?? null;
            }

            $datasource = new \Mdtt\DataSource\Json(
                $rawDataSourceDefinition['data'],
                $rawDataSourceDefinition['selector'],
                $this->jsonDataSourceUtility,
            );
            $datasource->setUsername($username);
            $datasource->setPassword($password);
            $datasource->setProtocol($protocol);

            return $datasource;
        }

        throw new SetupException(sprintf("Unexpected data source type %s and data source definition passed.", $type));
    }

    /**
     * @param array<string> $rawDataSourceDefinition
     * @param array<string, array<string, array<string, string>>> $specification
     *
     * @return void
     * @throws \Mdtt\Exception\SetupException
     */
    private function doValidateDatabase(
        array $rawDataSourceDefinition,
        array $specification
    ): void {
        $dbValidator = new Database();
        $isValid = $dbValidator->validate($rawDataSourceDefinition, $specification);
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
