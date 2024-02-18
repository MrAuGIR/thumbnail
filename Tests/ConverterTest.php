<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\ImagickConverter;
use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Option;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    public function testInitConverter() : void {

        $imageJpeg = ImageFaker::getImage("test.jpg");
        $imageCad  = ImageFaker::getImage("test.cad");

        $converter = new ImagickConverter();

        $this->assertFalse($converter->support($imageCad));
        $this->assertTrue($converter->support($imageJpeg));

        $option = new Option("-resize","125x25");
        $configuration = new Configuration([$option]);
        $configuration->setOutputPath(__DIR__."/images/thumbnail/");

        $converter->setConfiguration($configuration);

        $command = "convert ".escapeshellarg($imageJpeg->getPath())." -resize 125x25 ".escapeshellarg($configuration->getOutputFullPath($imageJpeg));

        $this->assertEquals($command,$converter->commandToExecute($imageJpeg));
    }
}