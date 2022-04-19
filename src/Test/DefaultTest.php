<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Mdtt\Exception\ExecutionException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

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

        /** @var string|int $sourceValue */
        $sourceValue = $sourceData[$this->getSourceField()];
        /** @var string|int $destinationValue */
        $destinationValue = $destinationData[$this->getDestinationField()];

        $this->getLogger()->info("Comparing source with destination.", [
            'Source' => $sourceValue,
            'Destination' => $destinationValue,
        ]);


        if ($this->getTransform() !== null) {
            $sourceValue = $this->getTransform()->process($sourceValue);

            $this->getLogger()->notice(
                "Applied transform on source. Comparing source with destination.",
                [
                    'Source' => $sourceValue,
                    'Destination' => $destinationValue,
                    'Transform' => $this->getTransform()->name()
                ]
            );
        }

        try {
            Assert::assertSame(
                $sourceValue,
                $destinationData[$this->getDestinationField()]
            );

            $this->getLogger()->notice("Source and destination matches.", [
                "Source" => $sourceValue,
                "Destination" => $destinationData,
            ]);
        } catch (ExpectationFailedException) {
            $this->getLogger()->emergency("Source and destination does not match.", [
                "Source" => $sourceValue,
                "Destination" => $destinationData,
            ]);
        }
    }
}
