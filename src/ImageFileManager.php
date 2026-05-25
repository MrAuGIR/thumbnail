<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ForbiddenSourceException;
use Symfony\Component\Filesystem\Filesystem;

class ImageFileManager
{
    private const ALLOWED_SCHEMES = ['http', 'https'];

    private Filesystem $fileSystem;

    /**
     * @param string[] $allowedHosts Empty list = any host allowed (the scheme is still enforced).
     * @param int $fetchTimeout      Network timeout, in seconds, for remote fetches.
     * @param int $maxFileSize       Maximum downloaded payload size, in bytes.
     */
    public function __construct(
        private readonly array $allowedHosts = [],
        private readonly int $fetchTimeout = 10,
        private readonly int $maxFileSize = 10485760,
    )
    {
        $this->fileSystem = new Filesystem();
    }

    /**
     * Downloads a remote (http/https) image into a temporary file.
     *
     * @param string $url
     * @return string
     * @throws CreateTmpFileException
     * @throws ForbiddenSourceException
     */
    public function createResource(string $url): string
    {
        $this->assertAllowed($url);

        $context = stream_context_create([
            'http' => [
                'timeout'         => $this->fetchTimeout,
                'follow_location' => 1,
                'max_redirects'   => 3,
            ],
        ]);

        // Read one byte past the limit so an oversized payload is detected, not silently truncated.
        $content = @file_get_contents($url, false, $context, 0, $this->maxFileSize + 1);

        if (false === $content) {
            throw new CreateTmpFileException(sprintf('Unable to fetch remote image "%s".', $url));
        }

        if (strlen($content) > $this->maxFileSize) {
            throw new ForbiddenSourceException(sprintf('Remote image "%s" exceeds the maximum allowed size of %d bytes.', $url, $this->maxFileSize));
        }

        if (false === $input = tempnam($path = sys_get_temp_dir(), 'thumb_')) {
            throw new CreateTmpFileException(sprintf('Error created tmp file in "%s".', $path));
        }

        $this->fileSystem->dumpFile($input, $content);

        return $input;
    }

    public function cleaner(string $tmpFile): void
    {
        if (is_file($tmpFile)) {
            unlink($tmpFile);
        }
    }

    /**
     * Enforces the security policy before any network access: only http/https
     * schemes, and — when configured — an explicit host allow-list.
     *
     * @throws ForbiddenSourceException
     */
    private function assertAllowed(string $url): void
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw new ForbiddenSourceException(sprintf('Scheme "%s" is not allowed for remote sources (only %s).', $scheme, implode('/', self::ALLOWED_SCHEMES)));
        }

        if ([] === $this->allowedHosts) {
            return;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (!in_array($host, array_map('strtolower', $this->allowedHosts), true)) {
            throw new ForbiddenSourceException(sprintf('Host "%s" is not in the configured allow-list.', $host));
        }
    }
}
