<?php

namespace Mdtt\Definition;

interface Definition
{
    /**
     * Runs the tests.
     * @return void
     */
    public function runTests(): void;

    /**
     * Runs smoke tests.
     * @return void
     */
    public function runSmokeTests(): void;
}
