<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use Mdtt\Report;
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
     * @param \Mdtt\Report $report *
     *
     * @inheritDoc
     */
    public function runSmokeTests(Report $report): void
    {
        $assertionCount = 0;
        $failureCount = 0;

        $source = $this->getSource();
        $destination = $this->getDestination();

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        $sourceRowCounts = iterator_count($sourceIterator);
        $destinationRowCounts = iterator_count($destinationIterator);

        try {
            $assertionCount++;

            Assert::assertSame(
                $sourceRowCounts,
                $destinationRowCounts
            );

            $this->logger->notice("Source row count matches with destination row count.", [
              'Source row count' => $sourceRowCounts,
              'Destination row count' => $destinationRowCounts,
            ]);
        } catch (ExpectationFailedException) {
            $failureCount++;

            $this->logger->emergency("Source row count does not matches with destination row count.", [
              'Source row count' => $sourceRowCounts,
              'Destination row count' => $destinationRowCounts,
            ]);
        }

        $report->setNumberOfAssertions($assertionCount);
        $report->setNumberOfFailures($failureCount);
        $report->setSourceRowCount($sourceRowCounts);
        $report->setDestinationRowCount($destinationRowCounts);
    }

    /**
     * @param \Mdtt\Report $report *
     *
     * @inheritDoc
     */
    public function runTests(Report $report): void
    {
        $assertionCount = 0;
        $failureCount = 0;
        $sourceCount = 0;
        $destinationCount = 0;

        $source = $this->getSource();
        $destination = $this->getDestination();

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        // Combining the iterators is required so that the tests can be run for every returned item.
        $combinedIterators = new \MultipleIterator();
        $combinedIterators->attachIterator($sourceIterator);
        $combinedIterators->attachIterator($destinationIterator);

        foreach ($combinedIterators as [$sourceValue, $destinationValue]) {
            $sourceCount++;
            $destinationCount++;

            foreach ($this->getTests() as $test) {
                $assertionCount++;

                if ($test->execute($sourceValue, $destinationValue)) {
                    $failureCount++;
                }
            }
        }

        while ($sourceIterator->valid()) {
            $sourceCount++;
            $sourceIterator->next();
        }

        while ($destinationIterator->valid()) {
            $destinationCount++;
            $destinationIterator->next();
        }

        $report->setNumberOfAssertions($assertionCount);
        $report->setNumberOfFailures($failureCount);
        $report->setSourceRowCount($sourceCount);
        $report->setDestinationRowCount($destinationCount);
    }
}
