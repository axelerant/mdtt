<?php

namespace Mdtt\LoadDefinition;

use Symfony\Component\Filesystem\Exception\IOException;

class DefaultLoader implements Load
{
    /**
     * @inheritDoc
     */
    public function scan(): iterable
    {
        $ymlTestDefinitions = glob("tests/*.yml", GLOB_ERR);
        if ($ymlTestDefinitions === false) {
            throw new IOException("Error occurred while loading test definitions");
        }

        $yamlTestDefinitions = glob("tests/*.yaml", GLOB_ERR);
        if ($yamlTestDefinitions === false) {
            throw new IOException("Error occurred while loading test definitions");
        }

        return array_merge([], $ymlTestDefinitions, $yamlTestDefinitions);
    }
}
