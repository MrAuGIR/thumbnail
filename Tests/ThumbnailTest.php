<?php

namespace MrAuGir\Thumbnail\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

class ThumbnailTest extends TestCase
{
    public function testThumbnailPath() : void {
        $inputPath = "public/assets/test.jpg";

        $this->assertEquals("public/assets/thumbnails/thumb_test.jpg",$inputPath);
    }
}