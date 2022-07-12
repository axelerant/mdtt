<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

class Database implements Type
{
    /**
     * @inheritDoc
     */
    public function validate(
        array $rawDataSourceDefinition,
        ?array $databaseSpecification
    ): bool {
        $isDatabaseSpecified = isset($rawDataSourceDefinition['database'], $databaseSpecification);

        if (!$isDatabaseSpecified) {
            return false;
        }

        /** @var string $databaseName */
        $databaseName = $rawDataSourceDefinition['database'];

        return isset(
            $databaseSpecification[$databaseName]['database'],
            $databaseSpecification[$databaseName]['username'],
            $databaseSpecification[$databaseName]['password'],
            $databaseSpecification[$databaseName]['host'],
            $databaseSpecification[$databaseName]['port']
        );
    }
}
