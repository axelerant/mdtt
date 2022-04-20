<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Log\LoggerInterface;

class DefaultDefinition extends Definition
{
    private string $description;
    private string $group;

    private LoggerInterface $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * @inheritDoc
     */
    public function runSmokeTests(): void
    {
        $source = $this->getSource();
        $destination = $this->getDestination();
        $this->logger->info(sprintf("Running smoke tests of definition id: %s", $this->getId()));

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        $sourceRowCounts = iterator_count($sourceIterator);
        $destinationRowCounts = iterator_count($destinationIterator);

        try {
            Assert::assertSame(
                $sourceRowCounts,
                $destinationRowCounts
            );

            $this->logger->notice("Source row count matches with destination row count.", [
              'Source row count' => $sourceRowCounts,
              'Destination row count' => $destinationRowCounts,
            ]);
        } catch (ExpectationFailedException) {
            $this->logger->emergency("Source row count does not matches with destination row count.", [
              'Source row count' => $sourceRowCounts,
              'Destination row count' => $destinationRowCounts,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function runTests(): void
    {
        $source = $this->getSource();
        $destination = $this->getDestination();
        $this->logger->info(sprintf("Running the tests of definition id: %s", $this->getId()));

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        // Combining the iterators is required so that the tests can be run for every returned item.
        $combinedIterators = new \MultipleIterator();
        $combinedIterators->attachIterator($sourceIterator);
        $combinedIterators->attachIterator($destinationIterator);

        foreach ($combinedIterators as [$sourceValue, $destinationValue]) {
            foreach ($this->getTests() as $test) {
                $test->execute($sourceValue, $destinationValue);
            }
        }

        try {
            Assert::assertTrue(!$sourceIterator->valid() && !$destinationIterator->valid());

            $this->logger->notice("Source row count matches with destination row count.");
        } catch (ExpectationFailedException) {
            $this->logger->emergency("Source row count does not matches with destination row count.");
        }
    }
}
