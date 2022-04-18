<?php

declare(strict_types=1);

namespace Mdtt\LoadDefinition;

use Mdtt\Definition\DefaultDefinition;
use Mdtt\Definition\Validate\DataSource\Validator;
use Mdtt\Exception\SetupException;
use Mdtt\Test\DefaultTest;
use Mdtt\Transform\PluginManager;
use Mdtt\Transform\Transform;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Yaml;

class DefaultLoader implements Load
{
    private LoggerInterface $logger;
    private Validator $dataSourceValidator;
    private PluginManager $transformPluginManager;
    /**
     * @var array<string, Transform>
     */
    private array $transformPlugins;

    public function __construct(LoggerInterface $logger, Validator $validator, PluginManager $transformPluginManager)
    {
        $this->logger = $logger;
        $this->dataSourceValidator = $validator;
        $this->transformPluginManager = $transformPluginManager;
    }

    /**
     * @inheritDoc
     */
    public function scan(array $locationPatterns): array
    {
        $rawTestDefinitions = [];

        foreach ($locationPatterns as $locationPattern) {
            $testDefinitions = glob($locationPattern, GLOB_ERR);

            if ($testDefinitions === false) {
                throw new IOException("Error occurred while loading test definitions");
            }

            $rawTestDefinitions[] = $testDefinitions;
        }

        return array_merge([], ...$rawTestDefinitions);
    }

    /**
     * @inheritDoc
     */
    public function validate(array $rawTestDefinitions): iterable
    {
        /** @var array<array<string>>|array<array<array<string>>> $testDefinitions */
        $testDefinitions = array_map(static function ($testDefinition) {
            return Yaml::parseFile($testDefinition);
        }, $rawTestDefinitions);
        $parsedTestDefinitions = [];

        foreach ($testDefinitions as $testDefinition) {
            $this->doValidate($testDefinition);

            $parsedTestDefinition = new DefaultDefinition($this->logger);

            /** @var string $id */
            $id = $testDefinition['id'];
            $parsedTestDefinition->setId($id);

            /** @var array<string> $sourceInformation */
            $sourceInformation = $testDefinition['source'];
            try {
                $sourceData = $this->dataSourceValidator->validate("source", $sourceInformation);
                $parsedTestDefinition->setSource($sourceData);
            } catch (SetupException $exception) {
                $this->logger->alert($exception->getMessage());
            }

            /** @var array<string> $destinationInformation */
            $destinationInformation = $testDefinition['destination'];
            try {
                $destinationData = $this->dataSourceValidator->validate("destination", $destinationInformation);
                $parsedTestDefinition->setDestination($destinationData);
            } catch (SetupException $exception) {
                $this->logger->alert($exception->getMessage());
            }

            /** @var array<array<string>> $tests */
            $tests = $testDefinition['tests'];
            /** @var array<\Mdtt\Test\Test> $parsedTests */
            $parsedTests = [];
            foreach ($tests as $test) {
                /** @var string $sourceField */
                $sourceField = $test['sourceField'];
                /** @var string $destinationField */
                $destinationField = $test['destinationField'];
                $testInstance = new DefaultTest(
                    $sourceField,
                    $destinationField,
                    $this->logger
                );

                if (isset($test['transform'])) {
                    if (!isset($this->transformPlugins[$test['transform']])) {
                        $transformPlugin = $this->transformPluginManager->loadById($test['transform']);
                        $this->transformPlugins[$transformPlugin->name()] = $transformPlugin;
                    } else {
                        $transformPlugin = $this->transformPlugins[$test['transform']];
                    }

                    $testInstance->setTransform($transformPlugin);
                }

                $parsedTests[] = $testInstance;
            }
            $parsedTestDefinition->setTests($parsedTests);

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

        if (empty($parsedTestDefinition['tests']) && !is_array($parsedTestDefinition['tests'])) {
            throw new SetupException("Test definition tests are missing");
        }
        /** @var array<array<string>> $tests */
        $tests = $parsedTestDefinition['tests'];
        foreach ($tests as $test) {
            if (empty($test['sourceField']) || empty($test['destinationField'])) {
                throw new SetupException("Test definition tests are missing");
            }
        }
    }
}
