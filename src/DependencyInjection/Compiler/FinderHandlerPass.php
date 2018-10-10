<?php

namespace Paknahad\JsonApiBundle\DependencyInjection\Compiler;

use Paknahad\JsonApiBundle\Helper\Filter\Filter\FinderHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FinderHandlerPass.
 */
class FinderHandlerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(FinderHandler::class)) {
            return;
        }

        $definition = $container->findDefinition(FinderHandler::class);
        $taggedServices = $container->findTaggedServiceIds('paknahad.json_api.finder');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addFinder', array(new Reference($id)));
        }
    }
}
