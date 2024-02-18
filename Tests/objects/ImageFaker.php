<?php

namespace MrAuGir\Thumbnail\Tests\objects;

use MrAuGir\Thumbnail\Model\Image;

class ImageFaker
{
    /**
     * Image Test
     * @param string $filename
     * @return Image
     */
    public static function getImage(string $filename) : Image {
        $path = __DIR__ . "/../images/".$filename;

        return new Image($path);
    }
}