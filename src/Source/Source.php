<?php

namespace Mdtt\Source;

interface Source
{
    /**
     * Returns the source data.
     * @return void
     */
    public function processData(): void;
}
