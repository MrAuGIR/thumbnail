<?php

namespace MrAuGir\Thumbnail\DependencyInjection;

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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->createConvertersService($configs[0]['converter']);
    }

    private function createConvertersService(array $converters)
    {
        foreach ($converters as $id => $conf) {

        }
    }
}