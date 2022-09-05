<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Mdtt\Exception\ExecutionException;
use PHPUnit\Framework\Assert;

class DefaultTest extends Test
{

    /**
     * @inheritDoc
     */
    public function execute(array $sourceData, array $destinationData): bool
    {
        $sourceFields = explode('/', $this->getSourceField());
        $destinationFields = explode('/', $this->getDestinationField());

        if (!$this->issetField($sourceData, $sourceFields)) {
            throw new ExecutionException("Source field could not be found in the source data.");
        }

        if (!$this->issetField($destinationData, $destinationFields)) {
            throw new ExecutionException("Destination field could not be found in the destination data.");
        }

        /** @var string|int $sourceValue */
        $sourceValue = array_reduce(
            $sourceFields,
            static fn (array $carry, string $key) => $carry[$key],
            $sourceData
        );
        /** @var string|int $destinationValue */
        $destinationValue = array_reduce(
            $destinationFields,
            static fn (array $carry, string $key) => $carry[$key],
            $destinationData
        );

        if ($this->getTransform() !== null) {
            $sourceValue = $this->getTransform()->process($sourceValue);
        }

        Assert::assertSame(
            $sourceValue,
            $destinationValue,
            sprintf("Source: `%s`\nDestination: `%s`", $sourceValue, $destinationValue)
        );

        return true;
    }

    /**
     * @param  array<string, numeric-string|array<string, numeric-string>>|numeric-string  $data
     * @param  array<string>  $fields
     *
     * @return bool
     */
    private function issetField(mixed $data, array $fields): bool
    {
        $key = array_shift($fields);
        if ($key === null) {
            return true;
        }

        $test = isset($data[$key]);

        return $test && $this->issetField($data[$key], $fields);
    }
}
