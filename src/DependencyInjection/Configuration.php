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
                // Presentation-layer fallback (NF2): used by the serving controller when
                // generation fails. The Engine itself keeps throwing — the fallback is a
                // decision of the controller, not the core.
                ->scalarNode('placeholder')
                    ->info('Absolute path to an image served when generation fails and fallback = placeholder. Optional.')
                    ->defaultNull()
                ->end()
                ->enumNode('fallback')
                    ->info("How the serving controller reacts to a failed generation: 'placeholder' (serve the configured image), 'source' (redirect to the original URL when it is http/https), 'none' (a 1x1 transparent pixel).")
                    ->values(['placeholder', 'source', 'none'])
                    ->defaultValue('source')
                ->end()
                ->scalarNode('cache_control')
                    ->info('Cache-Control header set on a successfully served thumbnail (and on the placeholder).')
                    ->defaultValue('public, max-age=31536000, immutable')
                ->end()
            ->end();
        ;


        return $treeBuilder;
    }
}