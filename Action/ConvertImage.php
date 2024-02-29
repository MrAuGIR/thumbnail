<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Action\Input\ConvertImageInput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Engine;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknowSourceImageException;
use MrAuGir\Thumbnail\Factory\ImageFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConvertImage
{
    public function __construct(
        private readonly Engine            $engine,
        private readonly ConverterResolver $converterResolver,
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
        $image = ImageFactory::create($path);
        $converter = $this->converterResolver->resolve($converter);

        $outputPath = $this->engine->processConvertion($image,$converter);

        return new BinaryFileResponse($outputPath,200, ['Content-Type' => "image/jpeg"]);
    }
}