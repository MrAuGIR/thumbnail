<?php

namespace MrAuGir\Thumbnail\DependencyInjection\Factory;

use MrAuGir\Thumbnail\Model\Option;
use Symfony\Component\DependencyInjection\Definition;

class OptionDefinitionFactory
{
    /**
     * @param array $conf
     * @return Definition
     */
    public function createDefinition(array $conf) : Definition {
        $definition = new Definition();
        $definition->setClass(Option::class);
        $definition->addArgument($conf['name']);
        $definition->addArgument($conf['value']);

        return $definition;
    }
}