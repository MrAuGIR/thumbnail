<?php

namespace MrAuGir\Thumbnail\Command;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Engine;
use MrAuGir\Thumbnail\Factory\ImageFactory;

abstract class ConvertImage
{
    public function __construct(
        protected Engine            $engine,
        protected ConverterResolver $converterResolver,
        protected ImageFactory      $imageFactory,
    )
    {
    }

    abstract public function executeFromInput(ConvertImageInput $input) : iterable;
}