<?php

namespace MrAuGir\Thumbnail\Factory;

use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Model\Configuration;

class ConverterFactory
{
    /**
     * @param string $binaryName
     * @param Configuration $configuration
     * @return Converter
     */
    public static function create(string $binaryName,Configuration $configuration) : Converter {
        $converter = new BinaryConverter();
        $converter->binaryName = $binaryName;
        $converter->setConfiguration($configuration);

        return $converter;
    }
}