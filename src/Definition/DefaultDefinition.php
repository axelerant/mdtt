<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use Mdtt\Report;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultDefinition extends Definition
{
    private string $description;
    private string $group;

    private LoggerInterface $logger;
    private OutputInterface $output;

    public function __construct(LoggerInterface $logger, OutputInterface $output)
    {
        $this->logger = $logger;
        $this->output = $output;
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
    }

    /**
     * @param \Mdtt\Report $report *
     *
     * @inheritDoc
     */
    public function runTests(Report $report): void
    {
    }
}
