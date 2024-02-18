<?php

namespace MrAuGir\Thumbnail\Tests\objects;

use MrAuGir\Thumbnail\Converter;
use MrAuGir\Thumbnail\Converter\ImagickConverter;
use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Image;
use MrAuGir\Thumbnail\Model\Option;

class ImageFaker
{
    public const OUTPUT_PATH_TEST = __DIR__."/../images/thumbnail/";
    /**
     * Image Test
     * @param string $filename
     * @return Image
     */
    public static function getImage(string $filename) : Image {
        $path = realpath(__DIR__ . "/../images/".$filename);

        return new Image($path);
    }

    /**
     * @return Configuration
     */
    public static function getConfiguration() : Configuration
    {
        return (new Configuration())
            ->addOption(self::getOption("-resize","125x25"))
            ->setOutputPath(self::OUTPUT_PATH_TEST);
    }

    /**
     * @param string $name
     * @param string $value
     * @return Option
     */
    public static function getOption(string $name, string $value) : Option {
        return new Option($name,$value);
    }

    /**
     * @return Converter
     */
    public static function getConverter() : Converter {
        $converter = new ImagickConverter();
        $converter->setConfiguration(self::getConfiguration());

        return $converter;
    }
}