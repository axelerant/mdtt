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
    public function runTests(): void
    {
        $source = $this->getSource();
        $destination = $this->getDestination();
        $this->logger->info(sprintf("Running the tests of definition id: %s", $this->id));

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        /** @var array<string>|array<int> $sourceValue */
        foreach ($sourceIterator as $sourceValue) {
            /** @var array<string>|array<int> $destinationValue */
            $destinationValue = $destinationIterator->current();
            $testResult = true;

            foreach ($this->getTests() as $test) {
                $testResult = ($testResult && $test->execute($sourceValue, $destinationValue));
            }

            if ($testResult) {
                $destinationIterator->next();
            }
        }

        try {
            Assert::assertTrue(
                !$sourceIterator->valid() && !$destinationIterator->valid(),
                "Number of source items does not match number of destination items."
            );
        } catch (ExpectationFailedException $exception) {
            $this->logger->emergency($exception->getMessage());
        }
    }
}
