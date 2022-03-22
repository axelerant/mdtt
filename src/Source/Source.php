<?php

namespace Mdtt\Source;

abstract class Source
{
    private string $type;
    protected string $data;

    public function __construct(string $type, string $data)
    {
        $this->type = $type;
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
