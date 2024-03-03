<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;

class InputFactory
{
    public static function supportedInput(): string
    {
        return ConvertImageInput::class;
    }

    public function createFromRequest(string $convert,string $path) : ConvertImageInput
    {
        return new ConvertImageInput($convert,$path);
    }
}