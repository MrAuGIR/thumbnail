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
        return new BinaryFileResponse($this->pathOutput, 200, ['Content-Type' => $this->detectContentType()]);
    }

    /**
     * Derives the Content-Type from the produced file instead of assuming PNG:
     * converters commonly output jpeg (ext: jpeg), gif, webp, … (F4).
     */
    private function detectContentType(): string
    {
        if (is_file($this->pathOutput) && false !== ($mime = @mime_content_type($this->pathOutput)) && '' !== $mime) {
            return $mime;
        }

        // Fallback when the file is unreadable: guess from the extension.
        return match (strtolower(pathinfo($this->pathOutput, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'avif'        => 'image/avif',
            default       => 'application/octet-stream',
        };
    }
}
