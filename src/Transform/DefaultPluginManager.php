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

        require "tests/mdtt/src/Plugin/Transform/$pluginId.php";

        /** @var \Mdtt\Transform\Transform $pluginInstance */
        $pluginInstance = new $pluginId();
        return $pluginInstance;
    }
}
