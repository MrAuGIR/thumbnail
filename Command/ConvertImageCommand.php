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
        $image = $this->imageFactory->create($input->getPath());
        $converter = $this->converterResolver->resolve($input->getConverter());

        try {
            yield $this->engine->processConvertion($image, $converter);
        } finally {
            // Remove the downloaded temp source once the conversion is done (F2).
            $this->imageFactory->cleanup($image);
        }
    }
}