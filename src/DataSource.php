<?php

namespace Mdtt;

use Iterator;

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
     * @return Iterator
     */
    abstract public function getItem(): Iterator;
}
