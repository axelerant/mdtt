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
        array $specification
    ): bool {
        $isDatabaseSpecified = isset($rawDataSourceDefinition['database']);

        if (!$isDatabaseSpecified) {
            return false;
        }

        /** @var string $databaseName */
        $databaseName = $rawDataSourceDefinition['database'];
        /** @var array<string, array<string, string>> $databaseSpecification */
        $databaseSpecification = $specification['databases'];

        return isset(
            $databaseSpecification[$databaseName]['database'],
            $databaseSpecification[$databaseName]['username'],
            $databaseSpecification[$databaseName]['password'],
            $databaseSpecification[$databaseName]['host'],
            $databaseSpecification[$databaseName]['port']
        );
    }
}
