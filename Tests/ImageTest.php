<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Factory\ImageFactory;
use MrAuGir\Thumbnail\ImageFileManager;
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

    public function testDetectSource() : void
    {
        // Only http/https are treated as remote URLs.
        $this->assertEquals(Image\Source::URL, ImageFactory::detectSource("https://picsum.photos/200/300"));
        $this->assertEquals(Image\Source::URL, ImageFactory::detectSource("http://example.com/cover.jpg"));

        // file://, ftp://, phar://, etc. must NOT be accepted — neither as a URL nor as a
        // local path: is_file() honours stream wrappers, so these would otherwise be an LFI/SSRF bypass.
        $this->assertEquals(Image\Source::UNKNOW, ImageFactory::detectSource("file:///etc/passwd"));
        $this->assertEquals(Image\Source::UNKNOW, ImageFactory::detectSource("ftp://example.com/cover.jpg"));
        $this->assertEquals(Image\Source::UNKNOW, ImageFactory::detectSource("phar:///tmp/x.phar/cover.jpg"));

        $path = realpath(__DIR__ . "/images/test.jpg");
        $this->assertEquals(Image\Source::ABSOLUTE, ImageFactory::detectSource($path));
    }

    public function testFactoryImageFromLocalPath() : void
    {
        $factory = new ImageFactory(new ImageFileManager());

        $path = realpath(__DIR__ . "/images/test.jpg");
        $img = $factory->create($path);

        $this->assertInstanceOf(Image::class, $img);
        $this->assertFalse($img->isTemporary());
        $this->assertEquals('image/jpeg', $img->getTypeMime());
    }

    public function testCleanupRemovesTemporaryImage() : void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'thumb_test_');
        copy(realpath(__DIR__ . "/images/test.jpg"), $tmp);

        $image = new Image($tmp, true);
        $this->assertTrue($image->isTemporary());

        (new ImageFactory(new ImageFileManager()))->cleanup($image);

        $this->assertFileDoesNotExist($tmp);
    }
}