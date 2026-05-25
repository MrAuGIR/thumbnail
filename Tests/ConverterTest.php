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

        $expected = ['convert', $imageJpeg->getPath(), '-resize', '125x25', $converter->getOutputPathForSource($imageJpeg->getSourceId())];

        $this->assertSame($expected, $converter->getCommand($imageJpeg));
    }

    public function testUseFactory() : void {

        $configuration = ImageFaker::getConfiguration();
        $image = ImageFaker::getImage("test.jpg");

        $converter = ConverterFactory::create('convert',$configuration);

        $expected = ['convert', $image->getPath(), '-resize', '125x25', $converter->getOutputPathForSource($image->getSourceId())];

        $this->assertInstanceOf(Converter::class,$converter);
        $this->assertTrue($converter->support($image));
        $this->assertSame($expected, $converter->getCommand($image));
    }

    public function testCommandIsArgvWithoutShellInterpretation() : void {

        $configuration = new Configuration([new Option('-resize', '200x300>')]);
        $configuration->setOutputPath(__DIR__."/images/thumbnail/");

        $converter = new BinaryConverter('convert');
        $converter->setConfiguration($configuration);

        $image = ImageFaker::getImage("test.jpg");
        $command = $converter->getCommand($image);

        // The metacharacter stays a literal, standalone argv element: no shell, no escaping, no redirection.
        $this->assertSame('convert', $command[0]);
        $this->assertSame($image->getPath(), $command[1]);
        $this->assertContains('200x300>', $command);
        $this->assertSame($converter->getOutputPathForSource($image->getSourceId()), end($command));
    }

    public function testOutputPathIsDeterministicAndConfigSensitive() : void {

        $makeConverter = function (string $binary, array $options): BinaryConverter {
            $configuration = new Configuration($options);
            $configuration->setOutputPath('/var/thumbnails/');
            $converter = new BinaryConverter($binary);
            $converter->setConfiguration($configuration);
            return $converter;
        };

        $source = 'https://example.com/cover.jpg';
        $base = $makeConverter('convert', [new Option('-resize', '200x')]);

        // Deterministic: same source + same config => same path.
        $this->assertSame($base->getOutputPathForSource($source), $base->getOutputPathForSource($source));
        $this->assertStringStartsWith('/var/thumbnails/thumb_', $base->getOutputPathForSource($source));
        $this->assertStringEndsWith('.jpg', $base->getOutputPathForSource($source));

        // Sensitive to options, binary, and source.
        $otherOptions = $makeConverter('convert', [new Option('-resize', '400x')]);
        $otherBinary  = $makeConverter('magick', [new Option('-resize', '200x')]);

        $this->assertNotSame($base->getOutputPathForSource($source), $otherOptions->getOutputPathForSource($source));
        $this->assertNotSame($base->getOutputPathForSource($source), $otherBinary->getOutputPathForSource($source));
        $this->assertNotSame($base->getOutputPathForSource($source), $base->getOutputPathForSource('https://example.com/other.jpg'));
    }

    public function testUserConverter() : void {

        $converter = new BinaryConverter("convert");
        $this->assertEquals(null,$converter->getId());

        $converter = new BinaryConverter("converter","thumbnail_web_800x800");
        $this->assertEquals("thumbnail_web_800x800",$converter->getId());
    }
}