<?php

namespace Pharmaline\PhlAbc\DependencyInjection;

use Pharmaline\PhlAbc\Security\Service\VoterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class VoterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(VoterRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(VoterRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('phlabc.voter');

        foreach ($taggedServices as $id => $tags) {
            $serviceDefinition = $container->getDefinition($id);

            if ($serviceDefinition->isAbstract()) {
                continue;
            }

            $definition->addMethodCall('registerVoter', [
                new Reference($id),
                $serviceDefinition->getClass(),
            ]);
        }
    }
}
