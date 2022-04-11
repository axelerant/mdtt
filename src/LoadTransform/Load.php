<?php

namespace Mdtt\LoadTransform;

use Mdtt\Transform\Transform;

interface Load
{
    /**
     * Scans all transform plugins and instantiates them.
     * @return Transform[]
     */
    public function scan(): array;
}
