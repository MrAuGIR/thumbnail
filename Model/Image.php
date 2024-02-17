<?php

namespace MrAuGir\Thumbnail\Model;

use http\Exception\InvalidArgumentException;

class Image
{
    /**
     * @param string $path
     */
    public function __construct(private string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf("Image : %s doesn't exist",$path));
        }
    }
}