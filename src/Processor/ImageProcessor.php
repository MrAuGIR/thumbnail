<?php

namespace MrAuGir\Thumbnail\Processor;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Engine;

abstract class ImageProcessor
{
    public function __construct(
        protected Engine            $engine,
        protected ConverterResolver $converterResolver,
    )
    {
    }

    abstract public function executeFromInput(ConvertImageInput $input) : iterable;
}