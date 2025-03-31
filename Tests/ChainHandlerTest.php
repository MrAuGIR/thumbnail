<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;

class ChainHandlerTest extends TestCase
{
    public function testAddConverter() : void {

        $converterChain = new ConverterChain();
        $converterChain->add(ImageFaker::getConverter());
        $converterChain->add(ImageFaker::getConverter());

        $this->assertCount(2, $converterChain->getChain());

        $this->assertIsIterable($converterChain);

        foreach ($converterChain as $converter) {
            $this->assertInstanceOf(Converter::class,$converter);
        }
    }
}