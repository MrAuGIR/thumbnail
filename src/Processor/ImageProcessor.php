<?php

namespace MrAuGir\Thumbnail\Processor;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\EngineInterface;

abstract class ImageProcessor
{
    public function __construct(
        protected EngineInterface   $engine,
        protected ConverterResolver $converterResolver,
    )
    {
    }

    abstract public function executeFromInput(ConvertImageInput $input) : iterable;
}