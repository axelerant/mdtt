<?php

declare(strict_types=1);

namespace Mdtt;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('MDTT', '1.0.0');
    }
}
