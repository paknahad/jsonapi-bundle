<?php

namespace Devleand\JsonApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('json_api');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->scalarNode('documentationSchema')->defaultValue('swagger')->end()
            ->scalarNode('controllerNamespace')->defaultValue('Controller\\')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
