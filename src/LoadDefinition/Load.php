<?php

namespace Mdtt\LoadDefinition;

interface Load
{
    /**
     * Scans the test directory for test definitions.
     *
     * @return array<string>
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Mdtt\Exception\SetupException
     */
    public function scan(): array;

    /**
     * Parses and validates the test definitions.
     *
     * @return array<\Mdtt\Definition\Definition>
     * @throws \Mdtt\Exception\SetupException
     */
    public function validate(): iterable;
}
