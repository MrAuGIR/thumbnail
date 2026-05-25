<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Factory\ConfigurationFactory;
use MrAuGir\Thumbnail\Factory\OptionFactory;
use MrAuGir\Thumbnail\Model\Configuration;
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

        $outputPath = __DIR__."/images/thumbnail/thumb_out.jpg";

        $expected = [
            $imageJpeg->getPath(),
            '-resize', '125x25',
            '-quality',
            $outputPath,
        ];

        $this->assertSame($expected, $configuration->getCommandArguments($imageJpeg, $outputPath));
    }

    public function testFactory() :void {
        /**
         * Factory Option
         */
        try {
            $failOption = ['label' => "-resize", "value" => ""];
            $option = OptionFactory::create($failOption);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class,$e);
           // $this->expectException(\InvalidArgumentException::class);
        }
        $successOption = ['name' => "-resize","value" => "125x25"];
        $option =OptionFactory::create($successOption);
        $this->assertEquals("-resize",$option->getName());
        $this->assertEquals("125x25",$option->getValue());
        $this->assertEquals("-resize 125x25",$option->getLineOption());

        /**
         * Factory configuration
         */
        $arrayConfig = [
            "prefix" => "thumb_25x125_",
            "options" => [
                0 => ["name" => "-resize", "value" => "125x25"],
                1 => ["name" => "quality", "value" => null]
            ],
            "outputPath" => __DIR__."/images/thumbnail/",
            "ext" => "png"
        ];

        $configuration = ConfigurationFactory::create($arrayConfig);
        $this->assertInstanceOf(Configuration::class,$configuration);
        $this->assertEquals("thumb_25x125_",$configuration->getPrefix());
        $this->assertEquals("png",$configuration->getExt());
    }
}