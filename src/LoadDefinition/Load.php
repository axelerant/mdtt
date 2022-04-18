<?php

namespace Mdtt\LoadDefinition;

interface Load
{
    /**
     * Scans the provided pattern for test definitions.
     *
     * @param array<string> $locationPatterns
     *
     * @return array<string> Raw test definition.
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Mdtt\Exception\SetupException
     */
    public function scan(array $locationPatterns): array;

    /**
     * Parses and validates the raw test definitions.
     *
     * @param array<string> $rawTestDefinitions
     *
     * @return array<\Mdtt\Definition\Definition>
     * @throws \Mdtt\Exception\SetupException
     */
    public function validate(array $rawTestDefinitions): iterable;
}
