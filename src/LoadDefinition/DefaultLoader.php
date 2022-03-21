<?php

namespace Mdtt\LoadDefinition;

use Mdtt\Exception\MissingTestDefinition;
use Symfony\Component\Filesystem\Exception\IOException;

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
            throw new MissingTestDefinition("No test definitions found.");
        }

        return $testDefinitions;
    }
}
