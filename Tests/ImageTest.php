<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Model\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testCreateImageFromPath() : void
    {
        $path = __DIR__ . "/images/test.jpg";

        $image = new Image($path);

        $this->assertInstanceOf(Image::class,$image);
        $this->assertEquals("image/jpeg",$image->getTypeMime());
        $this->assertEquals("jpg",$image->getExtension());
        $this->assertEquals("test",$image->getFileName());
    }
}