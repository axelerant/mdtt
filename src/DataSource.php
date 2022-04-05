<?php

namespace Mdtt;

abstract class DataSource
{
    protected string $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * Returns an item from the source.
     *
     * @return array<string>|array<int>
     */
    abstract public function getItem(): ?array;
}
