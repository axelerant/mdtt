<?php

namespace Mdtt\Destination;

interface Destination
{
    /**
     * Returns the destination data.
     *
     * @return array<string>
     */
    public function processData(): iterable;
}
