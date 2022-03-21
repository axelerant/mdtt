<?php

namespace Mdtt\LoadDefinition;

use Mdtt\Destination\Query as QueryDestination;
use Mdtt\Exception\TestSetupException;
use Mdtt\Source\Query as QuerySource;
use Mdtt\TestDefinition\DefaultTestDefinition;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Yaml;

class DefaultLoader implements Load
{
    /**
     * @inheritDoc
     */
    public function scan(): iterable
    {
        $ymlTestDefinitions = glob("tests/mdtt/*.yml", GLOB_ERR);
        if ($ymlTestDefinitions === false) {
            throw new IOException("Error occurred while loading test definitions");
        }

        $yamlTestDefinitions = glob("tests/mdtt/*.yaml", GLOB_ERR);
        if ($yamlTestDefinitions === false) {
            throw new IOException("Error occurred while loading test definitions");
        }

        $testDefinitions = array_merge([], $ymlTestDefinitions, $yamlTestDefinitions);
        if (!$testDefinitions) {
            throw new TestSetupException("No test definitions found.");
        }

        return $testDefinitions;
    }

    /**
     * @inheritDoc
     */
    public function validate(): iterable
    {
        $testDefinitions = array_map(static function ($testDefinition) {
            return Yaml::parseFile($testDefinition);
        }, $this->scan());
        $parsedTestDefinitions = [];

        foreach ($testDefinitions as $testDefinition) {
            $this->doValidate($testDefinition);

            $parsedTestDefinition = new DefaultTestDefinition();
            $parsedTestDefinition->setId($testDefinition['id']);

            // TODO: Improve the source initialization.
            // This will become bigger while adding support for json, xml, etc.
            if ($testDefinition['source']['type'] === "query") {
                $parsedTestDefinition->setSource((new QuerySource()));
            }

            // TODO: Improve the destination initialization.
            // This will become bigger while adding support for json, xml, etc.
            if ($testDefinition['destination']['type'] === "query") {
                $parsedTestDefinition->setDestination((new QueryDestination()));
            }

            if (!empty($testDefinition['description'])) {
                $parsedTestDefinition->setDescription($testDefinition['description']);
            }

            if (!empty($testDefinition['group'])) {
                $parsedTestDefinition->setGroup($testDefinition['group']);
            }

            $parsedTestDefinitions[] = $parsedTestDefinition;
        }

        return $parsedTestDefinitions;
    }

    /**
     * Validates the test definitions.
     * @param array $parsedTestDefinition
     */
    private function doValidate(array $parsedTestDefinition): void
    {
        if (empty($parsedTestDefinition['id'])) {
            throw new TestSetupException("Test definition id is missing");
        }

        // TODO: Further validate source types to SQL, JSON, XML, CSV.
        if (empty($parsedTestDefinition['source']['type'] ||
          empty($parsedTestDefinition['source']['data']))) {
            throw new TestSetupException("Test definition source is missing");
        }

        // TODO: Further validate destination types to SQL, JSON, XML.
        if (empty($parsedTestDefinition['destination']['type']) ||
          empty($parsedTestDefinition['destination']['data'])) {
            throw new TestSetupException("Test definition destination is missing");
        }

        if (empty($parsedTestDefinition['tests'])) {
            throw new TestSetupException("Test definition tests are missing");
        }
    }
}
