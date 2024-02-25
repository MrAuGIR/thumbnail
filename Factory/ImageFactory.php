<?php

namespace MrAuGir\Thumbnail\Factory;

use MrAuGir\Thumbnail\Exception\UnknowSourceImage;
use MrAuGir\Thumbnail\ImageFileManager;
use MrAuGir\Thumbnail\Model\Image;

class ImageFactory
{
    /**
     * @param string $path
     * @return Image
     * @throws UnknowSourceImage
     */
    public static function create(string $path) : Image {
        return match (self::detectSource($path)) {
            Image\Source::URL => self::createTempFileFromUrl($path) ,
            Image\Source::ABSOLUTE => new Image($path) ,
            Image\Source::UNKNOW => throw new UnknowSourceImage(sprintf("unknow source image %s",$path)),
        };
    }

    public static function detectSource(string $path) : Image\Source {
        if (self::detectUrl($path)) {
            return Image\Source::URL;
        } elseif (self::detectAbsolutePath($path)) {
            return Image\Source::ABSOLUTE;
        }
        return Image\Source::UNKNOW;
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function detectUrl(string $path) : bool {
        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function detectAbsolutePath(string $path) : bool {
        return is_file($path);
    }

    public static function createTempFileFromUrl(string $url) : string {
        $imageManager = new ImageFileManager();
        return $imageManager->createTemporyImage($url);
    }
}