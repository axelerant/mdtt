<?php

namespace Mdtt\LoadDefinition;

use Mdtt\Definition\DefaultDefinition;
use Mdtt\Destination\Query as QueryDestination;
use Mdtt\Exception\SetupException;
use Mdtt\Source\Query as QuerySource;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Yaml;

class DefaultLoader implements Load
{
    /**
     * @inheritDoc
     */
    public function scan(): array
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
            throw new SetupException("No test definitions found.");
        }

        return $testDefinitions;
    }

    /**
     * @inheritDoc
     */
    public function validate(): iterable
    {
        /** @var array<array<string>>|array<array<array<string>>> $testDefinitions */
        $testDefinitions = array_map(static function ($testDefinition) {
            return Yaml::parseFile($testDefinition);
        }, $this->scan());
        $parsedTestDefinitions = [];

        foreach ($testDefinitions as $testDefinition) {
            $this->doValidate($testDefinition);

            $parsedTestDefinition = new DefaultDefinition();

            /** @var string $id */
            $id = $testDefinition['id'];
            $parsedTestDefinition->setId($id);

            $parsedTestDefinition->setSource((new QuerySource()));
            $parsedTestDefinition->setDestination((new QueryDestination()));

            /** @var ?string $description */
            $description = $testDefinition['description'] ?? null;
            if ($description) {
                $parsedTestDefinition->setDescription($description);
            }

            /** @var ?string $group */
            $group = $testDefinition['group'] ?? null;
            if ($group) {
                $parsedTestDefinition->setGroup($group);
            }

            $parsedTestDefinitions[] = $parsedTestDefinition;
        }

        return $parsedTestDefinitions;
    }

    /**
     * Validates the test definitions.
     * @param array<string>|array<array<string>> $parsedTestDefinition
     */
    private function doValidate(array $parsedTestDefinition): void
    {
        if (empty($parsedTestDefinition['id'])) {
            throw new SetupException("Test definition id is missing");
        }

        // TODO: Further validate source types to SQL, JSON, XML, CSV.
        if (is_array($parsedTestDefinition['source']) &&
          (empty($parsedTestDefinition['source']['type']) ||
          empty($parsedTestDefinition['source']['data']))) {
            throw new SetupException("Test definition source is missing");
        }

        // TODO: Further validate destination types to SQL, JSON, XML.
        if (is_array($parsedTestDefinition['destination']) &&
          (empty($parsedTestDefinition['destination']['type']) ||
          empty($parsedTestDefinition['destination']['data']))) {
            throw new SetupException("Test definition destination is missing");
        }

        if (empty($parsedTestDefinition['tests'])) {
            throw new SetupException("Test definition tests are missing");
        }
    }
}
