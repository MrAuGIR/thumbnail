<?php

namespace MrAuGir\Thumbnail\DependencyInjection\Factory;

use MrAuGir\Thumbnail\Converter\ConverterChain;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConverterChainDefinitionFactory
{
    /**
     * @param string $id
     * @param array $conf
     * @return Definition|null
     */
    public function createDefinition(string $id, array $conf) : ?Definition {

        $definition = new Definition();
        $definition->setClass(ConverterChain::class);
        $definition->setPublic(true);
        $definition->setAutowired(true);

        $this->configureDefinition($definition,$conf);

        return $definition;
    }

    /**
     * @param Definition $definition
     * @param array $conf
     * @return void
     */
    private function configureDefinition(Definition $definition,array $conf) : void {

        foreach ($conf as $idConverter) {
            $alias =  sprintf('thumbnail.converter.%s', $idConverter);
            $definition->addMethodCall('add',[new Reference($alias)]);
        }
    }
}