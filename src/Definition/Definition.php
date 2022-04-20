<?php

namespace Mdtt\Definition;

use Mdtt\DataSource\DataSource;
use Mdtt\Report;
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
     *
     * @param \Mdtt\Report $report *
     *
     * @return void
     */
    abstract public function runTests(Report $report): void;

    /**
     * @return \Mdtt\Test\Test[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @param \Mdtt\DataSource\DataSource $source
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
     * @return \Mdtt\DataSource\DataSource
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
     * @param \Mdtt\DataSource\DataSource $destination
     */
    public function setDestination(DataSource $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return \Mdtt\DataSource\DataSource
     */
    public function getDestination(): DataSource
    {
        return $this->destination;
    }

    /**
     * Runs smoke tests.
     *
     * @param \Mdtt\Report $report *
     *
     * @return void
     */
    abstract public function runSmokeTests(Report $report): void;
}
