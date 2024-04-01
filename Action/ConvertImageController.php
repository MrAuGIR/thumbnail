<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Action\Output\ConvertImageOutput;
use MrAuGir\Thumbnail\Command\ConvertImageCommand;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class ConvertImageController
{
    public function __construct(
        private readonly InputFactory $inputFactory,
        private readonly ConvertImageCommand $convertImageCmd
    )
    {}

    /**
     * @throws CreateTmpFileException
     * @throws UnknowSourceImageException
     * @throws ConverterNotFoundException|ImageConvertException
     */
    public function __invoke(Request $request,string $converter, string $path) : BinaryFileResponse
    {
        $input = $this->inputFactory->createFromRequest($converter,$path);

        $outputPath = ($this->convertImageCmd->executeFromInput($input))->current();

        return (new ConvertImageOutput($outputPath))->getBinaryFileResponse();
    }
}