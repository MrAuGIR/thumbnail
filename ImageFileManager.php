<?php

namespace MrAuGir\Thumbnail;

use Symfony\Component\Uid\Uuid;

class ImageFileManager
{
    /**
     * @param $url
     * @return string
     */
    public function createTemporyImage($url) : string {
        $content = file_get_contents($url);
        $path = "/var/www/html/var/tmp/tmp_file_".Uuid::v4().'.jpeg';
        file_put_contents($path,$content);
        return $path;
    }
}