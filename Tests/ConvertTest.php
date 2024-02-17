<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Converter\ImagickConverter;
use MrAuGir\Thumbnail\Model\Image;
use PHPUnit\Framework\TestCase;

class ConvertTest extends TestCase
{
    public function testInitConverter() : void {

        $path = __DIR__ . "/images/test.jpg";
        $pathOutput = __DIR__."/images/thumbnail/thumb_test.jpg";
        $pathCad = __DIR__ . "/images/test.cad";
        $imageJpeg = new Image($path);
        $imageCad  = new Image($pathCad);

        $converter = new ImagickConverter();

        $this->assertFalse($converter->support($imageCad));
        $this->assertTrue($converter->support($imageJpeg));

        $command = "convert ".$path." -resize 125x25 ".$pathOutput;

        $this->assertEquals($command,$converter->commandToExecute());
    }
}