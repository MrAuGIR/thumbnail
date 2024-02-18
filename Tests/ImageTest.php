<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Model\Image;
use MrAuGir\Thumbnail\Tests\objects\ImageFaker;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testCreateImageFromPath() : void
    {
        $image = ImageFaker::getImage("test.jpg");

        $this->assertInstanceOf(Image::class,$image);
        $this->assertEquals("image/jpeg",$image->getTypeMime());
        $this->assertEquals("jpg",$image->getExtension());
        $this->assertEquals("test",$image->getFileName());
    }
}