<?php

declare(strict_types=1);

namespace Mdtt\Test;

use Mdtt\Exception\ExecutionException;
use PHPUnit\Framework\Assert;
use InvalidArgumentException;

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
     * Recursively checks if an offset exists in a nested array.
     *
     * This function is designed to handle nested arrays and verify the existence of
     * a specified offset (string key) within them. It performs recursive checks on
     * nested arrays to ensure that the offset exists at each level.
     *
     * @param array<string, numeric-string|array<string, numeric-string>> $data
     *     The data array to check for the offset in.
     * @param array<string> $fields
     *     An array of string keys representing the path to the desired offset.
     *
     * @return bool
     *     Returns `true` if the specified offset exists in the nested array,
     *     `false` otherwise.
     *
     * @throws InvalidArgumentException
     *     If the input data structure is not as expected.
     */
    private function issetField(array $data, array $fields): bool
    {
        // Get the next key to check
        $key = array_shift($fields);

        // If there are no more keys to check, the offset exists
        if ($key === null) {
            return true;
        }

        // Check if the key exists in the data array
        if (!array_key_exists($key, $data)) {
            return false;
        }

        // Make sure that the key value is an array of strings
        if (!is_array($data[$key]) || !$this->isArrayOfStrings($data[$key])) {
            throw new InvalidArgumentException("Data structure is not as expected.");
        }

        // Recursively check the next level
        return $this->issetField($data[$key], $fields);
    }

    /**
     * Checks if an iterable value is an array containing only strings.
     *
     * @param iterable<string> $value
     *     The array to check.
     *
     * @return bool
     *     Returns `true` if the iterable value is an array containing only strings, `false` otherwise.
     *
     * @throws InvalidArgumentException
     *     If the input value is not an array or contains non-string elements..
     */
    private function isArrayOfStrings(iterable $value): bool
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException("Input must be an array.");
        }

        foreach ($value as $item) {
            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }
}
