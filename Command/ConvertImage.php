<?php

namespace MrAuGir\Thumbnail\Command;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Engine;

abstract class ConvertImage
{
    public function __construct(
        protected readonly Engine            $engine,
        protected readonly ConverterResolver $converterResolver,
    )
    {
    }

    abstract public function executeFromInput(ConvertImageInput $input) : string;
}