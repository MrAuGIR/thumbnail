<?php

namespace MrAuGir\Thumbnail\Converter;

use MrAuGir\Thumbnail\Model\Image;

class BinaryConverter implements Converter
{
    use TraitConfiguration, TraitIdentifier;

    public string $binaryName = "convert";

    private static array $allowedMimeType = [
        'image/gif',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];

    /**
     * @param string $binaryName
     * @param string|null $id
     */
    public function __construct(string $binaryName = 'convert',string $id = null)
    {
        $this->binaryName = $binaryName;
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function support(Image $image): bool
    {
        return in_array($image->getTypeMime(),self::$allowedMimeType);
    }

    /**
     * @inheritDoc
     */
    public function commandToExecute(Image $image): string
    {
        return sprintf("%s %s",
            $this->binaryName,
            $this->configuration->getOtionsChain($image)
        );
    }
}