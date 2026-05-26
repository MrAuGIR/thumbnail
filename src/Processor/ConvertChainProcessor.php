<?php

namespace MrAuGir\Thumbnail\Processor;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterChainResolver;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\EngineInterface;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknownSourceImageException;
use MrAuGir\Thumbnail\Exception\UnsupportedImageTypeException;

class ConvertChainProcessor extends ImageProcessor
{
    public function __construct(
        protected EngineInterface   $engine,
        protected ConverterResolver $converterResolver,
        protected readonly ConverterChainResolver $converterChainResolver,
    )
    {
        parent::__construct($engine, $converterResolver);
    }

    /**
     * @param ConvertImageInput $input
     * @return iterable
     * @throws ConverterNotFoundException
     * @throws CreateTmpFileException
     * @throws ImageConvertException
     * @throws UnknownSourceImageException
     * @throws UnsupportedImageTypeException
     */
    public function executeFromInput(ConvertImageInput $input) : iterable
    {
        $chains = $this->converterChainResolver->resolve($input->getConverter());

        // Caches per converter, downloads the source at most once, cleans temp (F1/F2).
        yield from $this->engine->thumbnailAll($input->getPath(), $chains);
    }
}