<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use Mdtt\DataSource\DataSource;
use Mdtt\Test\Test;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Log\LoggerInterface;

class DefaultDefinition implements Definition
{
    private string $id;
    private string $description;
    private string $group;
    private DataSource $source;
    private DataSource $destination;
    /** @var array<Test> */
    private array $tests;
    private LoggerInterface $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Mdtt\Test\Test[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @param \Mdtt\Test\Test[] $tests
     */
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
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
     * @return \Mdtt\DataSource\DataSource
     */
    public function getSource(): DataSource
    {
        return $this->source;
    }

    /**
     * @param \Mdtt\DataSource\DataSource $source
     */
    public function setSource(DataSource $source): void
    {
        $this->source = $source;
    }

    /**
     * @return \Mdtt\DataSource\DataSource
     */
    public function getDestination(): DataSource
    {
        return $this->destination;
    }

    /**
     * @param \Mdtt\DataSource\DataSource $destination
     */
    public function setDestination(DataSource $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @inheritDoc
     */
    public function runSmokeTests(): void
    {
        $source = $this->getSource();
        $destination = $this->getDestination();
        $this->logger->info(sprintf("Running smoke tests of definition id: %s", $this->id));

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        $sourceRowCounts = iterator_count($sourceIterator);
        $destinationRowCounts = iterator_count($destinationIterator);

        try {
            Assert::assertSame(
                $sourceRowCounts,
                $destinationRowCounts,
                sprintf(
                    "Source row count: %d does not matches with destination row count: %d",
                    $sourceRowCounts,
                    $destinationRowCounts
                )
            );
        } catch (ExpectationFailedException $exception) {
            $this->logger->emergency($exception->getMessage(), [
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
        $this->logger->info(sprintf("Running the tests of definition id: %s", $this->id));

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
    }
}
