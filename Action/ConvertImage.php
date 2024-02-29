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
use Symfony\Component\Routing\Annotation\Route;

class ConvertImage
{
    public function __construct(
        private readonly InputFactory $inputFactory,
        private readonly ConvertImageCommand $convertImageFactory
    )
    {}

    /**
     * @throws CreateTmpFileException
     * @throws UnknowSourceImageException
     * @throws ConverterNotFoundException|ImageConvertException
     */
    #[Route("/thumbnail/call/{converter}/{path}", name: "mraugir_thumbnail_converter", requirements: ["path" => ".+" ], methods: ["GET"])]
    public function __invoke(Request $request,string $converter, string $path) : BinaryFileResponse
    {
        $input = $this->inputFactory->createFromRequest($converter,$path);

        $outputPath = $this->convertImageFactory->executeFromInput($input);

        return (new ConvertImageOutput($outputPath))->getBinaryFileResponse();
    }
}