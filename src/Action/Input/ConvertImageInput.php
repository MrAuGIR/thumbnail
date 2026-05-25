<?php

namespace MrAuGir\Thumbnail\Action\Input;

final class ConvertImageInput
{
    public function __construct(
        private readonly string $converter,
        private readonly string $path
    ){}

    /**
     * @return string
     */
    public function getConverter(): string
    {
        return $this->converter;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}