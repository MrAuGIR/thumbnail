<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Factory\ConverterFactory;
use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Option;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    public function testInitConverter() : void {

        $imageJpeg = ImageFaker::getImage("test.jpg");
        $imageCad  = ImageFaker::getImage("test.cad");

        $converter = new BinaryConverter("convert");

        $this->assertFalse($converter->support($imageCad));
        $this->assertTrue($converter->support($imageJpeg));

        $option = new Option("-resize","125x25");
        $configuration = new Configuration([$option]);
        $configuration->setOutputPath(__DIR__."/images/thumbnail/");

        $converter->setConfiguration($configuration);

        $command = "convert ".escapeshellarg($imageJpeg->getPath())." -resize 125x25 ".escapeshellarg($configuration->getOutputFullPath($imageJpeg));

        $this->assertEquals($command,$converter->commandToExecute($imageJpeg));
    }

    public function testUseFactory() : void {

        $configuration = ImageFaker::getConfiguration();
        $image = ImageFaker::getImage("test.jpg");

        $converter = ConverterFactory::create('convert',$configuration);

        $command = "convert ".escapeshellarg($image->getPath())." -resize 125x25 ".escapeshellarg($configuration->getOutputFullPath($image));

        $this->assertInstanceOf(Converter::class,$converter);
        $this->assertTrue($converter->support($image));
        $this->assertEquals($command,$converter->commandToExecute($image));
    }
}