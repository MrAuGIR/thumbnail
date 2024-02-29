<?php

namespace MrAuGir\Thumbnail\Action\Output;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ConvertImageOutput
{
    public function __construct(private readonly string $pathOutput)
    {
    }

    public function getBinaryFileResponse() : BinaryFileResponse
    {
        return new BinaryFileResponse($this->pathOutput,200,["Content-Type" => "image/png"]);
    }
}