<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use MrAuGir\Thumbnail\Factory\ImageFactory;
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

    /**
     * @return void
     * @throws UnknowSourceImageException|CreateTmpFileException
     */
    public function testFactoryImage() : void
    {
        // test creation depuis url
        $factory = new ImageFactory();
        $url = "https://picsum.photos/200/300";

        $isImage = $factory::detectSource($url);
        $this->assertEquals(Image\Source::URL,$isImage);

        $path =  realpath(__DIR__ . "/images/test.jpg");
        $isPath = $factory::detectSource($path);
        $this->assertEquals(Image\Source::ABSOLUTE,$isPath);

        $img = $factory::create($url);
        $this->assertInstanceOf(Image::class,$img);

        $this->assertEquals('',$img->getExtension()); // l'url n'a pas d'extension
        $this->assertEquals('image/jpeg',$img->getTypeMime());
    }
}