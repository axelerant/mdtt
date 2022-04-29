<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Mdtt\Exception\ExecutionException;
use PHPUnit\Framework\Assert;

class DefaultTest extends Test
{
    /**
     * @inheritDoc
     */
    public function execute(array $sourceData, array $destinationData): bool
    {
        if (!isset($sourceData[$this->getSourceField()])) {
            throw new ExecutionException("Source field could not be found in the source data.");
        }

        if (!isset($destinationData[$this->getDestinationField()])) {
            throw new ExecutionException("Destination field could not be found in the destination data.");
        }

        /** @var string|int $sourceValue */
        $sourceValue = $sourceData[$this->getSourceField()];
        /** @var string|int $destinationValue */
        $destinationValue = $destinationData[$this->getDestinationField()];

        if ($this->getTransform() !== null) {
            $sourceValue = $this->getTransform()->process($sourceValue);
        }

        Assert::assertSame(
            $sourceValue,
            $destinationValue,
        );

        return true;
    }
}
