<?php

namespace Mdtt\DataSource;

use Iterator;

abstract class DataSource
{
    protected string $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the datasource iterator.
     *
     * @return Iterator
     */
    abstract public function getIterator(): Iterator;
}
