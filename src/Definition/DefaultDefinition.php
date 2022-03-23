<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use Mdtt\DataSource;
use Webmozart\Assert\Assert;

class DefaultDefinition implements Definition
{
    private string $id;
    private string $description;
    private string $group;
    private DataSource $source;
    private DataSource $destination;

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
     * @return \Mdtt\DataSource
     */
    public function getSource(): DataSource
    {
        return $this->source;
    }

    /**
     * @param \Mdtt\DataSource $source
     */
    public function setSource(DataSource $source): void
    {
        $this->source = $source;
    }

    /**
     * @return \Mdtt\DataSource
     */
    public function getDestination(): DataSource
    {
        return $this->destination;
    }

    /**
     * @param \Mdtt\DataSource $destination
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

        $sourceData = $source->getItem();
        $destinationData = $destination->getItem();

        do {
            Assert::same($sourceData, $destinationData);

            $sourceData = $source->getItem();
            $destinationData = $destination->getItem();
        } while ($sourceData || $destinationData);
    }
}
