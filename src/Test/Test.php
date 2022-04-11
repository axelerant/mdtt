<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Mdtt\Transform\Transform;
use Psr\Log\LoggerInterface;

abstract class Test
{
    private string $sourceField;
    private string $destinationField;
    private LoggerInterface $logger;
    private Transform $transform;

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
     * @param \Mdtt\Transform\Transform|null $transform
     */
    public function __construct(
        string $sourceField,
        string $destinationField,
        LoggerInterface $logger,
        Transform $transform = null
    ) {
        $this->sourceField = $sourceField;
        $this->destinationField = $destinationField;
        $this->logger = $logger;
        $this->transform = $transform;
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
     * @return \Mdtt\Transform\Transform|null
     */
    public function getTransform(): ?Transform
    {
        return $this->transform;
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
