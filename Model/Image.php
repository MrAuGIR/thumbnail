<?php

namespace MrAuGir\Thumbnail\Model;

class Image
{
    /**
     * @param string $path
     * @param bool $temporary Whether $path is a temp file (e.g. a downloaded URL) that must be cleaned up afterwards.
     */
    public function __construct(
        private readonly string $path,
        private readonly bool $temporary = false,
    )
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf("Image : %s doesn't exist",$path));
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isTemporary(): bool
    {
        return $this->temporary;
    }

    /**
     * @return string
     */
    public function getTypeMime(): string
    {
        return mime_content_type($this->path);
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return pathinfo($this->getPath(),PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return pathinfo($this->path,PATHINFO_FILENAME);
    }
}