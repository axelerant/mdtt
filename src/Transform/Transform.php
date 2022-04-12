<?php

declare(strict_types=1);

namespace Mdtt\Transform;

interface Transform
{
    /**
     * Name of the plugin.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Alters the provided data.
     *
     * @param string|int $data
     *
     * @return string|int
     */
    public function process(mixed $data): mixed;
}
