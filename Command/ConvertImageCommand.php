<?php

namespace MrAuGir\Thumbnail\Command;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use MrAuGir\Thumbnail\Factory\ImageFactory;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure]
class ConvertImageCommand extends ConvertImage
{
    /**
     * @throws CreateTmpFileException
     * @throws ImageConvertException
     * @throws UnknowSourceImageException
     * @throws ConverterNotFoundException
     */
    public function executeFromInput(ConvertImageInput $input) : iterable
    {
        $image = ImageFactory::create($input->getPath());
        $converter = $this->converterResolver->resolve($input->getConverter());

        yield $this->engine->processConvertion($image,$converter);
        return;
    }
}