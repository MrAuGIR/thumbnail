<?php

namespace MrAuGir\Thumbnail\Tests\Kernel;

use MrAuGir\Thumbnail\ThumbnailBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

class ThumbnailTestKernel extends Kernel
{
    private array $converterClients = [];
    /**
     * @inheritDoc
     */
    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new ThumbnailBundle()];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader) : void
    {
        $converterClients = $this->converterClients;

        $loader->load(function (ContainerBuilder $container) use ($converterClients) {
            foreach ($converterClients as $service => $mock) {
                $container->setDefinition($service, new Definition())->setSynthetic(true);
            }
        });

        $loader->load(__DIR__.'/config/framework.yaml', 'yaml');
        $loader->load(__DIR__.'/config/thumbnail.yaml', 'yaml');
        $loader->load(__DIR__.'/config/services.yaml', 'yaml');
    }

    /**
     * @param array $converterClients
     * @return void
     */
    public function setConverterClients(array $converterClients): void
    {
        $this->converterClients = $converterClients;
    }

    public function getCacheDir(): string
    {
        return $this->createTmpDir('cache');
    }

    public function getLogDir(): string
    {
        return $this->createTmpDir('logs');
    }

    private function createTmpDir(string $type): string
    {
        $dir = sys_get_temp_dir().'/thumbnail/'.uniqid($type.'_', true);

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }
}