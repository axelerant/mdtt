<?php

declare(strict_types=1);

namespace Mdtt;

class Report
{
    private int $numberOfTestDefinitions = 0;
    private int $numberOfAssertions = 0;
    private int $numberOfFailures = 0;
    private int $sourceRowCount = 0;
    private int $destinationRowCount = 0;

    public function incrementNumberOfTestDefinitions(): void
    {
        ++$this->numberOfTestDefinitions;
    }

    public function incrementNumberOfAssertions(): void
    {
        ++$this->numberOfAssertions;
    }

    public function incrementNumberOfFailures(): void
    {
        ++$this->numberOfFailures;
    }

    public function incrementSourceRowCount():void
    {
        ++$this->sourceRowCount;
    }

    public function incrementDestinationRowCount(): void
    {
        ++$this->destinationRowCount;
    }

    /**
     * @param int $numberOfTestDefinitions
     */
    public function setNumberOfTestDefinitions(
        int $numberOfTestDefinitions
    ): void {
        $this->numberOfTestDefinitions = $numberOfTestDefinitions;
    }

    /**
     * @param int $numberOfAssertions
     */
    public function setNumberOfAssertions(int $numberOfAssertions): void
    {
        $this->numberOfAssertions = $numberOfAssertions;
    }

    /**
     * @param int $numberOfFailures
     */
    public function setNumberOfFailures(int $numberOfFailures): void
    {
        $this->numberOfFailures = $numberOfFailures;
    }

    /**
     * @param int $sourceRowCount
     */
    public function setSourceRowCount(int $sourceRowCount): void
    {
        $this->sourceRowCount = $sourceRowCount;
    }

    /**
     * @param int $destinationRowCount
     */
    public function setDestinationRowCount(int $destinationRowCount): void
    {
        $this->destinationRowCount = $destinationRowCount;
    }

    /**
     * @return int
     */
    public function getNumberOfTestDefinitions(): int
    {
        return $this->numberOfTestDefinitions;
    }

    /**
     * @return int
     */
    public function getNumberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }

    /**
     * @return int
     */
    public function getNumberOfFailures(): int
    {
        return $this->numberOfFailures;
    }

    /**
     * @return int
     */
    public function getSourceRowCount(): int
    {
        return $this->sourceRowCount;
    }

    /**
     * @return int
     */
    public function getDestinationRowCount(): int
    {
        return $this->destinationRowCount;
    }

    public function isFailure(): bool
    {
        return ($this->numberOfFailures) !== 0 || ($this->sourceRowCount !== $this->destinationRowCount);
    }
}
