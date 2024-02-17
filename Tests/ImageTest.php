<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Model\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testCreateImageFromPath() : void
    {
        $path = "images/test.jpg";

        $image = new Image($path);

        $this->assertInstanceOf(Image::class,$image);
    }
}