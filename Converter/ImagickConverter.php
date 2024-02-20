<?php

namespace MrAuGir\Thumbnail\Converter;

use MrAuGir\Thumbnail\Model\Image;

class ImagickConverter implements Converter
{
    use TraitConfiguration;

    public string $binaryName = "convert";

    private static array $allowedMimeType = [
        'image/gif',
		'image/jpeg',
		'image/jpg',
		'image/png'
    ];

    /**
     * @inheritDoc
     */
    public function support(Image $image): bool
    {
        return in_array($image->getTypeMime(),static::$allowedMimeType);
    }

    /**
     * @inheritDoc
     */
    public function commandToExecute(Image $image): string
    {
        return $this->binaryName." ".$this->configuration->getOtionsChain($image);
    }
}