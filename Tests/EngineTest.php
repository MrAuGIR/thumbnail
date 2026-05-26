<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Engine;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnsupportedImageTypeException;
use MrAuGir\Thumbnail\ExitCode;
use MrAuGir\Thumbnail\Factory\ImageFactory;
use MrAuGir\Thumbnail\ImageFileManager;
use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Option;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class EngineTest extends TestCase
{
    private function makeEngine(): Engine
    {
        return new Engine(new ImageFactory(new ImageFileManager()));
    }

    public function testConvertImage() : void {

        $image = ImageFaker::getImage("test.jpg");
        $configuration = ImageFaker::getConfiguration();

        $converter = new BinaryConverter("convert");
        $converter->setConfiguration($configuration);

        $output = $converter->getOutputPathForSource($image->getSourceId());
        // on supprime l'ancien fichier de tests
        if (file_exists($output)) {
            unlink($output);
            $this->assertFileDoesNotExist($output);
        }

        $process = new Process($converter->getCommand($image));
        $result = $process->run();

        $this->assertEquals(0,$result);

        $this->assertFileExists($output);
    }

    /**
     * @throws ImageConvertException
     */
    public function testEngine() : void {
        $engine = $this->makeEngine();
        $image = ImageFaker::getImage("test.jpg");

        $this->assertEquals(0,ExitCode::SUCCESS->value);
        $this->assertEquals(1,ExitCode::FAILURE->value);

        $engine->processConversion($image, ImageFaker::getConverter());
    }

    /**
     * A non-image source (here an empty .cad file) must be rejected up front with an
     * explicit exception, before the binary is ever invoked.
     */
    public function testProcessConversionRejectsUnsupportedMime() : void {
        $engine = $this->makeEngine();
        $image  = ImageFaker::getImage("test.cad"); // mime: application/x-empty

        $this->expectException(UnsupportedImageTypeException::class);
        $engine->processConversion($image, ImageFaker::getConverter());
    }

    /**
     * A cache hit must return the existing render WITHOUT downloading or
     * converting: we use a bogus binary that would fail if it were ever run.
     *
     * @throws ImageConvertException
     */
    public function testThumbnailShortCircuitsOnCacheHit() : void {
        $source = realpath(__DIR__."/images/test.jpg");

        $configuration = (new Configuration([new Option('-resize', '50x50')]))
            ->setOutputPath(__DIR__."/images/thumbnail/");
        $converter = new BinaryConverter('this-binary-does-not-exist');
        $converter->setConfiguration($configuration);

        $expected = $converter->getOutputPathForSource($source);
        file_put_contents($expected, 'cached'); // pre-populate the cache

        try {
            $result = $this->makeEngine()->thumbnail($source, $converter);
            $this->assertSame($expected, $result);
        } finally {
            @unlink($expected);
        }
    }
}
