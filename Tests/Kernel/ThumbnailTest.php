<?php

namespace MrAuGir\Thumbnail\Tests\Kernel;

use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\Model\Configuration;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

class ThumbnailTest extends WebTestCase
{
    /**
     * @throws Exception
     */
    protected function createThumbnailKernel(): ThumbnailTestKernel
    {
        (new Dotenv())->populate([
        ]);

        $kernel = new ThumbnailTestKernel('test', true);
        $kernel->setConverterClients($this->getClientMocks());
        $kernel->boot();

        $container = $kernel->getContainer()->get('test.service_container');
        foreach ($this->getClientMocks() as $service => $mock) {
            if ($mock) {
                $container->set($service, $mock);
            }
        }

        foreach ($this->getChainMocks() as $service => $mock) {
            if ($mock) {
                $container->set($service, $mock);
            }
        }

        return $kernel;
    }

    /**
     * @throws Exception
     */
    private function getClientMocks(): array
    {
        $convert = $this->createMock(BinaryConverter::class);
        $convert->setConfiguration($this->createMock(Configuration::class));
        $convert->method('setConfiguration')->willReturn($this->createMock(BinaryConverter::class));

        return [
            'convert_vignette' => $convert,
        ];
    }

    /**
     * @throws Exception
     */
    private function getChainMocks(): array
    {
        $chain = $this->createMock(ConverterChain::class);
        $chain->add($this->createMock(BinaryConverter::class));
        $chain->add($this->createMock(BinaryConverter::class));
        $chain->method('add')->willReturn($this->createMock(ConverterChain::class));

        return [
            'chain_web' => $chain
        ];
    }
}