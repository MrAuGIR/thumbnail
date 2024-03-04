<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Command\ConvertChainImageCommand;
use MrAuGir\Thumbnail\Command\ConvertImageCommand;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use Symfony\Component\HttpFoundation\Request;

class ConvertChainImageController
{
    public function __construct(
        private readonly InputFactory $inputFactory,
        private readonly ConvertChainImageCommand $convertImageCmd
    )
    {
    }

    /**
     * @throws CreateTmpFileException
     * @throws ImageConvertException
     * @throws UnknowSourceImageException
     * @throws ConverterNotFoundException
     */
    public function __invoke(Request $request, string $chain, string $path): void
    {
        $input = $this->inputFactory->createConvertChainFromRequest($chain,$path);

        $outputPath = $this->convertImageCmd->executeFromInput($input);
    }
}