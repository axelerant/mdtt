<?php

namespace Mdtt\TestDefinition;

interface TestDefinition
{
    /**
     * Runs the tests.
     * @return void
     */
    public function runTests(): void;
}
