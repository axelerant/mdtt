<?php

declare(strict_types=1);

namespace Mdtt\Destination;

use Mdtt\DataSource;

class Query extends DataSource
{
    /**
     * @inheritDoc
     */
    public function getItem(): ?array
    {
        return [];
    }
}
