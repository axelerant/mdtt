<?php

declare(strict_types=1);

namespace Mdtt\Definition\Validate\DataSource;

class Json implements Type
{
    /**
     * @inheritDoc
     */
    public function validate(array $rawDataSourceDefinition,
      array $specification): bool
    {
        return isset($rawDataSourceDefinition['selector']);
    }
}
