<?php

namespace MrAuGir\Thumbnail\DependencyInjection\Factory;
use MrAuGir\Thumbnail\Converter\BinaryConverter;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConverterDefinitionFactory
{
    /**
     * @param string $id
     * @param array $conf
     * @return Definition|null
     */
    public function createDefinition(string $id, array $conf) : ?Definition {

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $resolverConfig = $resolver->resolve($conf);

        $definition = new Definition();
        $definition->setPublic(true);
        $definition->setClass(BinaryConverter::class);
        $definition->addArgument($resolverConfig['binary']);

        // Configuration
        $this->configureDefinition($definition,$resolverConfig);

        $definition->setAutowired(true);
        return $definition;
    }

    /**
     * @param Definition $definition
     * @param array $config
     * @return void
     */
    private function configureDefinition(Definition $definition, array $config) : void {
        $configuraionFactory = new ConfigurationDefinitionFactory();
        $configuration = $configuraionFactory->createDefinition('',$config);
        $definition->addMethodCall('setConfiguration',[$configuration]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    private function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setRequired(['binary', 'configuration']);
        $resolver->setAllowedTypes('binary', 'string');
        $resolver->setAllowedTypes('configuration', 'array');
    }
}