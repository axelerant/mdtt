<?php

namespace Mdtt\Destination;

interface Destination
{
    /**
     * Returns the destination data.
     * @return void
     */
    public function processData(): void;
}
