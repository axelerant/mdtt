<?php

declare(strict_types=1);

namespace Mdtt\Test;

abstract class Test
{
    private string $sourceField;
    private string $destinationField;

    /**
     * @param string $sourceField
     * @param string $destinationField
     */
    public function __construct(string $sourceField, string $destinationField)
    {
        $this->sourceField = $sourceField;
        $this->destinationField = $destinationField;
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
