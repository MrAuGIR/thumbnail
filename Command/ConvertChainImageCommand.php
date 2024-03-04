<?php

namespace MrAuGir\Thumbnail\Command;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Command\ConvertImage;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterChainResolver;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Engine;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use MrAuGir\Thumbnail\Factory\ImageFactory;

class ConvertChainImageCommand extends ConvertImage
{
    public function __construct(
        protected readonly Engine            $engine,
        protected readonly ConverterResolver $converterResolver,
        protected readonly ConverterChainResolver $converterChainResolver
    )
    {
        parent::__construct($engine,$this->converterResolver);
    }

    /**
     * @param ConvertImageInput $input
     * @return \Generator
     * @throws ConverterNotFoundException
     * @throws CreateTmpFileException
     * @throws ImageConvertException
     * @throws UnknowSourceImageException
     */
    public function executeFromInput(ConvertImageInput $input) :\Generator
    {
        $image = ImageFactory::create($input->getPath());

        $chains = $this->converterChainResolver->resolve($input->getConverter());

        foreach ($chains as $converter) {
            yield $this->engine->processConvertion($image, $converter);
        }
    }
}