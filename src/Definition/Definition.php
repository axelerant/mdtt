<?php

namespace Mdtt\Definition;

use Mdtt\DataSource;
use Mdtt\Test\Test;

abstract class Definition
{
    /** @var array<Test> */
    private array $tests;

    private DataSource $destination;

    private string $id;

    private DataSource $source;

    /**
     * Runs the tests.
     * @return void
     */
    abstract public function runTests(): void;

    /**
     * @return \Mdtt\Test\Test[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @param \Mdtt\DataSource $source
     */
    public function setSource(DataSource $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return \Mdtt\DataSource
     */
    public function getSource(): DataSource
    {
        return $this->source;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param \Mdtt\Test\Test[] $tests
     */
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }

    /**
     * @param \Mdtt\DataSource $destination
     */
    public function setDestination(DataSource $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return \Mdtt\DataSource
     */
    public function getDestination(): DataSource
    {
        return $this->destination;
    }
}
