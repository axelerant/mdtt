<?php

namespace Mdtt\LoadDefinition;

interface Load
{
    /**
     * Scans the test directory for test definitions.
     *
     * @return array<string>
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function scan(): iterable;
}
