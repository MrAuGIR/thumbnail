<?php

namespace MrAuGir\Thumbnail\Model;

class Image
{
    /**
     * Stable identifier of the original source (URL or path). For a downloaded
     * URL this is the URL itself, NOT the random temp file name — so the cache
     * key derived from it is deterministic across requests.
     */
    private readonly string $sourceId;

    /**
     * @param string $path
     * @param bool $temporary Whether $path is a temp file (e.g. a downloaded URL) that must be cleaned up afterwards.
     * @param string|null $sourceId Original source identifier; defaults to $path for local sources.
     */
    public function __construct(
        private readonly string $path,
        private readonly bool $temporary = false,
        ?string $sourceId = null,
    )
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf("Image : %s doesn't exist",$path));
        }

        $this->sourceId = $sourceId ?? $path;
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
    public function getSourceId(): string
    {
        return $this->sourceId;
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