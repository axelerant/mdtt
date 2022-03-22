<?php

declare(strict_types=1);

namespace Mdtt\Destination;

class Query implements Destination
{
    /**
     * @inheritDoc
     */
    public function processData(): array
    {
        return [];
    }
}
