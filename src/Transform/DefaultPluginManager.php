<?php

declare(strict_types=1);

namespace Mdtt\Transform;

class DefaultPluginManager implements PluginManager
{
    /**
     * @inheritDoc
     */
    public function loadById(string $id): Transform
    {
        $pluginId = ucwords($id);
        /** @var \Mdtt\Transform\Transform */
        return new $pluginId();
    }
}
