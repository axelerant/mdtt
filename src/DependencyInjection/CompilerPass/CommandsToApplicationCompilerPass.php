<?php

declare(strict_types=1);

namespace Mdtt\DependencyInjection\CompilerPass;

use Mdtt\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandsToApplicationCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $applicationDefinition = $container->getDefinition(Application::class);

        foreach ($container->getDefinitions() as $name => $definition) {
            /** @var object|string $classDefinition */
            $classDefinition = $definition->getClass();

            if (is_a($classDefinition, Command::class, true)) {
                $applicationDefinition->addMethodCall('add', [new Reference($name)]);
            }
        }
    }
}
