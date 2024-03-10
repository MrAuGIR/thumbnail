<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Command\ConvertChainCommand;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ConvertChainController
{
    public function __construct(
        private readonly InputFactory             $inputFactory,
        private readonly ConvertChainCommand $convertImageCmd
    )
    {
    }

    /**
     * @throws CreateTmpFileException
     * @throws ImageConvertException
     * @throws UnknowSourceImageException
     * @throws ConverterNotFoundException
     */
    public function __invoke(Request $request, string $chain, string $path): JsonResponse
    {
        $input = $this->inputFactory->createConvertChainFromRequest($chain, $path);

        $outputPath = $this->convertImageCmd->executeFromInput($input);

        return new JsonResponse([]);
    }
}