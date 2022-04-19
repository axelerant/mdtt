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

        $this->getLogger()->info(sprintf(
            "Comparing source <info>%s</info> with destination <info>%s</info>",
            $sourceData[$this->getSourceField()],
            $destinationData[$this->getDestinationField()]
        ));

        /** @var string|int $sourceValue */
        $sourceValue = $sourceData[$this->getSourceField()];
        if ($this->getTransform() !== null) {
            $sourceValue = $this->getTransform()->process($sourceValue);

            $this->getLogger()->notice(sprintf(
                "Applied transform: %s on source. Comparing source %s with destination %s",
                $this->getTransform()->name(),
                $sourceValue,
                $destinationData[$this->getDestinationField()]
            ));
        }

        try {
            Assert::assertSame(
                $sourceValue,
                $destinationData[$this->getDestinationField()]
            );

            $this->getLogger()->notice("Source and destination matches", [
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
