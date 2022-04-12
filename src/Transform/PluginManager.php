<?php

namespace Mdtt\Transform;

interface PluginManager
{
    /**
     * Scans all transform plugins and instantiates them.
     *
     * @param string $id
     *
     * @return \Mdtt\Transform\Transform
     */
    public function loadById(string $id): Transform;
}
