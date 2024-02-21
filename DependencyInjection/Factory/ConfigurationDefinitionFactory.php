<?php

namespace MrAuGir\Thumbnail\DependencyInjection\Factory;

use MrAuGir\Thumbnail\Model\Configuration;
use Symfony\Component\DependencyInjection\Definition;

class ConfigurationDefinitionFactory
{
    /**
     * @param string $id
     * @param array $conf
     * @return Definition
     */
    public function createDefinition(string $id, array $conf) : Definition
    {
        $configuration = new Definition();
        $configuration->setClass(Configuration::class);
        // options
        $this->configureDefinition($configuration,$conf['configuration']['options']);

        $configuration->addArgument($conf['configuration']['outputPath']);
        $configuration->addMethodCall('setPrefix', [$conf['configuration']['prefix']]);
        $configuration->addMethodCall('setExtension', [$conf['configuration']['ext']]);

        return $configuration;
    }

    /**
     * @param Definition $definition
     * @param array $confOption
     * @return void
     */
    private function configureDefinition(Definition $definition, array $confOption) : void {
        $optionFactory = new OptionDefinitionFactory();
        $options = [];
        foreach ($confOption as $option) {
            $options[] = $optionFactory->createDefinition($option);
        }
        $definition->addArgument($options);
    }
}