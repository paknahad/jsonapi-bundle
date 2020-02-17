<?php


namespace Paknahad\JsonApiBundle\DependencyInjection;


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
            ->scalarNode('documentation')->defaultValue('swagger')->end()
            ->scalarNode('controller_namespace')->defaultValue('..\\Controller\\')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}