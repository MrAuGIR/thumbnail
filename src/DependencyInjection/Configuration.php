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
                    ->performNoDeepMerging()
                    ->children()
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
            ->end()
                ->arrayNode("chains")
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                // Security / robustness settings (S2, F5).
                ->arrayNode('allowed_hosts')
                    ->info('Allow-list of hosts authorized as remote sources. Empty = any host (the http/https scheme is always enforced).')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->integerNode('fetch_timeout')
                    ->info('Network timeout, in seconds, when downloading a remote source.')
                    ->defaultValue(10)
                ->end()
                ->integerNode('max_file_size')
                    ->info('Maximum size, in bytes, of a downloaded remote source.')
                    ->defaultValue(10485760)
                ->end()
                ->integerNode('process_timeout')
                    ->info('Maximum duration, in seconds, a conversion process may run.')
                    ->defaultValue(60)
                ->end()
            ->end();
        ;


        return $treeBuilder;
    }
}