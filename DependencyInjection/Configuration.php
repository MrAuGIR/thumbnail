<?php

namespace MrAuGir\Thumbnail\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder("thumbnail");

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->fixXmlConfig('converter')
            ->children()
                ->arrayNode('converters')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('path')->isRequired()->end()
                        ->scalarNode('binary')->isRequired()->end()
                        ->arrayNode('configuration')
                            ->children()
                                ->scalarNode('prefix')->isRequired()->end()
                                ->scalarNode('ext')->isRequired()->end()
                                ->arrayNode('options')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                        ->scalarNode('value')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->scalarNode('outputPath')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
            ->end()
            ->end();


        return $treeBuilder;
    }
}