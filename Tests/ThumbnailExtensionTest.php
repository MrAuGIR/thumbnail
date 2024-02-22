<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Tests\Kernel\ThumbnailTestKernel;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ThumbnailExtensionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testThumbnailBundle() : void {
        $kernel = $this->createThumbnailKernel();
        $container = $kernel->getContainer()->get('test.service_container');

        $convert = $container->get("convert_vignette");

        $this->assertInstanceOf(Converter::class,$convert);
    }

    /**
     * @throws Exception
     */
    private function createThumbnailKernel(): ThumbnailTestKernel
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
}