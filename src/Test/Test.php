<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Psr\Log\LoggerInterface;

abstract class Test
{
    private string $sourceField;
    private string $destinationField;
    private LoggerInterface $logger;

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string $sourceField
     * @param string $destinationField
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(string $sourceField, string $destinationField, LoggerInterface $logger)
    {
        $this->sourceField = $sourceField;
        $this->destinationField = $destinationField;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getSourceField(): string
    {
        return $this->sourceField;
    }

    /**
     * @return string
     */
    public function getDestinationField(): string
    {
        return $this->destinationField;
    }

    /**
     * Compare the source and the destination data.
     *
     * @param array<scalar> $sourceData
     * @param array<scalar> $destinationData
     *
     * @return void
     */
    abstract public function execute(array $sourceData, array $destinationData): void;
}
