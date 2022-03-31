<?php

namespace Mdtt\Definition\Validate\DataSource;

interface Type
{
    /**
     * Validates whether all required information are mentioned for the datasource definition.
     *
     * @param array<string> $rawDataSourceDefinition
     *
     * @return bool
     */
    public function validate(array $rawDataSourceDefinition): bool;
}
