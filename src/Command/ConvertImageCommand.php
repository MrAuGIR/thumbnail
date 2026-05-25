<?php

namespace MrAuGir\Thumbnail\Command;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
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
        $converter = $this->converterResolver->resolve($input->getConverter());

        // Engine::thumbnail short-circuits on a cache hit and handles temp cleanup (F1/F2).
        yield $this->engine->thumbnail($input->getPath(), $converter);
    }
}