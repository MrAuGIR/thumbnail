<?php

namespace MrAuGir\Thumbnail\DependencyInjection;

use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\DependencyInjection\Factory\ConverterChainDefinitionFactory;
use MrAuGir\Thumbnail\DependencyInjection\Factory\ConverterDefinitionFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ThumbnailExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->createConvertersService($config['converters'], $container);

        $this->createConverterChainService($config['chains'], $container);
    }

    /**
     * @param array $converters
     * @param ContainerBuilder $container
     * @return void
     */
    private function createConvertersService(array $converters, ContainerBuilder $container) : void
    {
        $converterFactory = new ConverterDefinitionFactory();
        foreach ($converters as $id => $conf) {
            // create a converter definition
            $definition = $converterFactory->createDefinition($id,$conf);
            $definition->addTag("mraugir.thumbnail.converter");
            $alias =  sprintf('thumbnail.converter.%s', $id);
            $container->setDefinition($id,$definition);
            $container->setDefinition($alias,$definition);
            $container->registerAliasForArgument($id, Converter::class, $id)->setPublic(false);
        }
    }

    /**
     * @param array $chains
     * @param ContainerBuilder $container
     * @return void
     */
    private function createConverterChainService(array $chains, ContainerBuilder $container) : void
    {
        $converterChainFactory = new ConverterChainDefinitionFactory();

        foreach ($chains as $id => $chain) {
            // create a converterChain definition
            $definition = $converterChainFactory->createDefinition($id, $chain);
            $definition->addTag("mraugir.thumbnail.chain");
            $alias = sprintf("thumbnail.chain.%s",$id);
            $container->setDefinition($id,$definition);
            $container->setDefinition($alias,$definition);
            $container->registerAliasForArgument($id, ConverterChain::class,$id)->setPublic(false);
        }
    }
}