<?php

namespace MrAuGir\Thumbnail;

use Symfony\Component\Filesystem\Filesystem;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;

class ImageFileManager
{
    private Filesystem $fileSystem;

    public function __construct()
    {
        $this->fileSystem = new Filesystem();
    }

    /**
     * @param $url
     * @return string
     * @throws CreateTmpFileException
     */
    public function createResource($url): string
    {
        $content = file_get_contents($url);

        if (false === $input = tempnam($path = sys_get_temp_dir(), 'thumb_')) {
            throw new CreateTmpFileException(sprintf('Error created tmp file in "%s".', $path));
        }

        $this->fileSystem->dumpFile($input,$content);

        return $input;
    }

    public function cleaner(string $tmpFile): void
    {
        unlink($tmpFile);
    }
}