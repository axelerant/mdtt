<?php

namespace Mdtt\Source;

abstract class Source
{
    protected string $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the source data.
     *
     * @return array<string>
     * @throws \Mdtt\Exception\SetupException
     */
    abstract public function processData(): array;
}
