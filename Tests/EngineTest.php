<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\ImagickConverter;
use MrAuGir\Thumbnail\Engine;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\ExitCode;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class EngineTest extends TestCase
{
    public function testConvertImage() : void {

        $image = ImageFaker::getImage("test.jpg");
        $configuration = ImageFaker::getConfiguration();
        // on supprime l'ancien fichier de tests
        if (file_exists($path = $configuration->getOutputFullPath($image))) {
            unlink($path);
            $this->assertFileDoesNotExist(__DIR__."/images/thumbnail/thumb_test.jpg",sprintf(" path thumb image : %s", $path));
        }

        $converter = new ImagickConverter();
        $converter->setConfiguration($configuration);

        $process = Process::fromShellCommandline($converter->commandToExecute($image));
        $result = $process->run();

        $this->assertEquals(0,$result);

        $this->assertFileExists($configuration->getOutputFullPath($image));

    }

    /**
     * @throws ImageConvertException
     */
    public function testEngine() : void {
        $engine = new Engine();
        $image = ImageFaker::getImage("test.jpg");
        $converter = ImageFaker::getConverter();

        $this->assertEquals(0,ExitCode::SUCCESS->value);
        $this->assertEquals(1,ExitCode::FAILURE->value);

        $engine->processConvertion($image, $converter);
    }
}