<?php

namespace MrAuGir\Thumbnail;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

class ImageFileManager
{

    private Filesystem $fileSystem;

    private string $tempDir;

    public function __construct()
    {
        $this->fileSystem = new Filesystem();
        $this->tempDir = "/var/www/html/var/tmp".DIRECTORY_SEPARATOR."thumbnails";
        $this->fileSystem->mkdir($this->tempDir);
    }

    /**
     * @param $url
     * @return string
     */
    public function createResource($url): string
    {
        $content = file_get_contents($url);
        $path = $this->tempDir.DIRECTORY_SEPARATOR."tmp_file_" . Uuid::v4() . '.jpeg';
        echo $path.PHP_EOL;
        $this->fileSystem->dumpFile($path,$content);

        return $path;
    }
}