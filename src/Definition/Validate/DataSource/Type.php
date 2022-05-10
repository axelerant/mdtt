<?php

namespace Mdtt\Definition\Validate\DataSource;

interface Type
{
    /**
     * Validates whether all required information are mentioned in the datasource definition.
     *
     * @param array<string> $rawDataSourceDefinition
     * @param array<string, array<string, string>> $specification
     *
     * @return bool
     */
    public function validate(
        array $rawDataSourceDefinition,
        array $specification
    ): bool;
}
