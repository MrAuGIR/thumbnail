<?php

namespace MrAuGir\Thumbnail\Message;

class ThumbnailMessage
{
    /**
     * @param string $pathImage
     * @param string $idConverter
     */
    public function __construct(
        private string $pathImage,
        private string $idConverter
    )
    {
    }

    /**
     * @return string
     */
    public function getPathImage(): string
    {
        return $this->pathImage;
    }

    /**
     * @param string $pathImage
     */
    public function setPathImage(string $pathImage): void
    {
        $this->pathImage = $pathImage;
    }

    /**
     * @return string
     */
    public function getIdConverter(): string
    {
        return $this->idConverter;
    }

    /**
     * @param string $idConverter
     */
    public function setIdConverter(string $idConverter): void
    {
        $this->idConverter = $idConverter;
    }
}