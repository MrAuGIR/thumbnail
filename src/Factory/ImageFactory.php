<?php

namespace MrAuGir\Thumbnail\Factory;

use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ForbiddenSourceException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use MrAuGir\Thumbnail\ImageFileManager;
use MrAuGir\Thumbnail\Model\Image;

class ImageFactory
{
    private const ALLOWED_SCHEMES = ['http', 'https'];

    public function __construct(private readonly ImageFileManager $imageFileManager)
    {
    }

    /**
     * @param string $path
     * @return Image
     * @throws UnknowSourceImageException|CreateTmpFileException|ForbiddenSourceException
     */
    public function create(string $path): Image
    {
        return match (self::detectSource($path)) {
            // Keep the original URL as the source id so the cache key stays stable
            // (the temp file name is random and must not drive caching).
            Image\Source::URL => new Image($this->imageFileManager->createResource($path), true, $path),
            Image\Source::ABSOLUTE => new Image($path),
            Image\Source::UNKNOW => throw new UnknowSourceImageException(sprintf("unknow source image %s", $path)),
        };
    }

    /**
     * Removes the temporary file backing an image when it was downloaded from a URL.
     */
    public function cleanup(Image $image): void
    {
        if ($image->isTemporary()) {
            $this->imageFileManager->cleaner($image->getPath());
        }
    }

    /**
     * @param string $path
     * @return Image\Source
     */
    public static function detectSource(string $path): Image\Source
    {
        if (self::detectUrl($path)) {
            return Image\Source::URL;
        } elseif (self::detectAbsolutePath($path)) {
            return Image\Source::ABSOLUTE;
        }
        return Image\Source::UNKNOW;
    }

    /**
     * A source is treated as a remote URL only when it is a valid URL *and*
     * uses an http/https scheme. This rejects file://, ftp://, gopher://, etc.,
     * closing the SSRF/LFI vector via the URL branch.
     *
     * @param string $path
     * @return bool
     */
    public static function detectUrl(string $path): bool
    {
        if (false === filter_var($path, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($path, PHP_URL_SCHEME));

        return in_array($scheme, self::ALLOWED_SCHEMES, true);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function detectAbsolutePath(string $path): bool
    {
        // is_file() honours stream wrappers, so is_file('file:///etc/passwd') is true
        // and is_file('http://…') even opens a network connection. Reject any
        // "scheme://" syntax here so the local-path branch can only ever hit a real
        // filesystem path — this closes the LFI/SSRF bypass around the URL guard.
        if (1 === preg_match('#^[a-z][a-z0-9+.\-]*://#i', $path)) {
            return false;
        }

        return is_file($path);
    }
}
