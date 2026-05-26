<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Action\Output\ConvertImageOutput;
use MrAuGir\Thumbnail\Processor\ConvertImageProcessor;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknownSourceImageException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class ConvertImageController
{
    public function __construct(
        private readonly InputFactory $inputFactory,
        private readonly ConvertImageProcessor $processor
    )
    {}

    /**
     * @throws CreateTmpFileException
     * @throws UnknownSourceImageException
     * @throws ConverterNotFoundException|ImageConvertException
     */
    public function __invoke(Request $request,string $converter, string $path) : BinaryFileResponse
    {
        $input = $this->inputFactory->createFromRequest($converter,$path);

        // Drain the generator fully so its finally block (temp-file cleanup) runs within the request.
        $outputPaths = iterator_to_array($this->processor->executeFromInput($input));
        $outputPath = reset($outputPaths);

        return (new ConvertImageOutput($outputPath))->getBinaryFileResponse();
    }
}