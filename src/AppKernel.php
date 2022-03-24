<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\DependencyInjection\CompilerPass\CommandsToApplicationCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{

    /**
     * @inheritDoc
     */
    public function registerBundles(): iterable
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/services.yml');
    }

    /**
     * @inheritDoc
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CommandsToApplicationCompilerPass);
    }
}
