<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

class Json implements Type
{
    /**
     * @inheritDoc
     */
    public function validate(
        array $rawDataSourceDefinition,
        ?array $httpSpecification
    ): bool {
        $isSelectorSpecified = isset($rawDataSourceDefinition['selector']);

        if (!$isSelectorSpecified) {
            return false;
        }

        $isAuthProtected = isset($rawDataSourceDefinition['credential'], $httpSpecification);

        if (!$isAuthProtected) {
            return true;
        }

        /** @var string $credentialKey */
        $credentialKey = $rawDataSourceDefinition['credential'];

        return isset(
            $httpSpecification[$credentialKey]['username'],
            $httpSpecification[$credentialKey]['password']
        );
    }
}
