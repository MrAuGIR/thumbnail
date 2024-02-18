<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Image;
use MrAuGir\Thumbnail\Model\Option;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testCreateConfiguration() : void {

        $imageJpeg = ImageFaker::getImage("test.jpg");

        $configuration = new Configuration();
        $this->assertInstanceOf(Configuration::class, $configuration);

        $option = new Option("-resize","125x25");

        $this->assertEquals("-resize",$option->getName());
        $this->assertNotEquals("125x35",$option->getValue());
        $this->assertEquals("-resize 125x25",$option->getLineOption());

        $optionWithoutValue = new Option("-quality");

        $this->assertEmpty($optionWithoutValue->getValue());

        $configuration
            ->addOption($option)
            ->addOption($optionWithoutValue)
            ->setOutputPath(__DIR__."/images/thumbnail/");

        $this->assertEquals(escapeshellarg(__DIR__."/images/thumbnail/thumb_test.jpg"),$configuration->getOutputFullPath($imageJpeg));

        $chain = trim(escapeshellarg($imageJpeg->getPath())." -resize 125x25 -quality ".$configuration->getOutputFullPath($imageJpeg));

        $this->assertEquals($chain, $configuration->getOtionsChain($imageJpeg));
    }
}