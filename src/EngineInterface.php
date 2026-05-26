<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Model\Image;

interface EngineInterface
{
    /**
     * Returns the thumbnail path for a source, generating it only when missing
     * (cache hit ⇒ the source is neither downloaded nor converted).
     *
     * @param string $source URL or local path of the original image.
     * @return string Deterministic, cached output path.
     * @throws ImageConvertException
     */
    public function thumbnail(string $source, Converter $converter): string;

    /**
     * Generates thumbnails for several converters sharing the same source,
     * downloading the source at most once.
     *
     * @param iterable<Converter> $converters
     * @return iterable<string> Output paths, in order.
     * @throws ImageConvertException
     */
    public function thumbnailAll(string $source, iterable $converters): iterable;

    /**
     * Runs the conversion for an already-resolved image and returns the output path.
     *
     * @throws ImageConvertException
     */
    public function processConversion(Image $image, Converter $converter): string;
}
