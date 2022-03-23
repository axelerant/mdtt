<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Mdtt\Exception\ExecutionException;
use Webmozart\Assert\Assert;

class DefaultTest extends Test
{
    /**
     * @inheritDoc
     */
    public function execute(array $sourceData, array $destinationData): void
    {
        if (!isset($sourceData[$this->getSourceField()])) {
            throw new ExecutionException("Source field could not be found in the source data.");
        }

        if (!isset($destinationData[$this->getDestinationField()])) {
            throw new ExecutionException("Destination field could not be found in the destination data.");
        }

        Assert::same($sourceData[$this->getSourceField()], $destinationData[$this->getDestinationField()]);
    }
}
