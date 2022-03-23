<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use Mdtt\DataSource;
use Mdtt\Destination\Destination;
use Webmozart\Assert\Assert;

class DefaultDefinition implements Definition
{
    private string $id;
    private string $description;
    private string $group;
    private DataSource $source;
    private Destination $destination;

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
     * @return \Mdtt\Destination\Destination
     */
    public function getDestination(): Destination
    {
        return $this->destination;
    }

    /**
     * @param \Mdtt\Destination\Destination $destination
     */
    public function setDestination(Destination $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @inheritDoc
     */
    public function runTests(): void
    {
        $sourceData = $this->getSource()->getItem();
        $destinationData = $this->getDestination()->processData();

        do {
            Assert::same($sourceData, $destinationData);
        } while ($sourceData || $destinationData);
    }
}
