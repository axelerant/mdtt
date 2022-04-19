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
    public function runTests(): void
    {
        $source = $this->getSource();
        $destination = $this->getDestination();
        $this->logger->info(sprintf("Running the tests of definition id: %s", $this->getId()));

        $sourceData = $source->getItem();
        $destinationData = $destination->getItem();

        // Combining the iterators is required so that the tests can be run for every returned item.
        $combinedDataSources = new \MultipleIterator();
        $combinedDataSources->attachIterator($sourceData);
        $combinedDataSources->attachIterator($destinationData);

        foreach ($combinedDataSources as [$sourceValue, $destinationValue]) {
            foreach ($this->getTests() as $test) {
                $test->execute($sourceValue, $destinationValue);
            }
        }

        try {
            Assert::assertTrue(
                !$sourceData->valid() && !$destinationData->valid(),
                "Number of source items does not match number of destination items."
            );
        } catch (ExpectationFailedException $exception) {
            $this->logger->emergency($exception->getMessage());
        }
    }
}
