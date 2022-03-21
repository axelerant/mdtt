<?php

declare(strict_types=1);

namespace Mdtt\Definition;

use Mdtt\Destination\Destination;
use Mdtt\Source\Source;

class DefaultDefinition implements Definition
{
    private string $id;
    private string $description;
    private string $group;
    private Source $source;
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
     * @return \Mdtt\Source\Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param \Mdtt\Source\Source $source
     */
    public function setSource(Source $source): void
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
        // TODO: Implement runTests() method.
    }
}
