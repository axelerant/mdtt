<?php

declare(strict_types=1);

namespace Mdtt\Transform;

abstract class Transform
{
    /**
     * Name of the plugin.
     *
     * @return string
     */
    abstract public function name(): string;

    /**
     * Alters the provided data.
     *
     * @param string|int $data
     *
     * @return string|int
     */
    abstract public function process(mixed $data): mixed;
}
