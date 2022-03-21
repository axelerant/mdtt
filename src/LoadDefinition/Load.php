<?php

namespace Mdtt\LoadDefinition;

interface Load
{
    /**
     * Scans the test directory for test definitions.
     *
     * @return array<string>
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Mdtt\Exception\MissingTestDefinition
     */
    public function scan(): iterable;
}
