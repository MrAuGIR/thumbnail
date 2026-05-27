<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Action\Output\ConvertImageOutput;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\EngineInterface;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\CreateTmpFileException;
use MrAuGir\Thumbnail\Exception\ForbiddenSourceException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Exception\UnknownSourceImageException;
use MrAuGir\Thumbnail\Exception\UnsupportedImageTypeException;
use MrAuGir\Thumbnail\Factory\ImageFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Same-origin endpoint that serves a generated thumbnail for `?src=<source>` and never
 * lets a failed generation break the page: on any caught error it returns a fallback
 * (placeholder / redirect to the source / transparent pixel) instead of a 500.
 *
 * The Engine keeps throwing — the fallback is a presentation-layer decision made here.
 */
class ServeThumbnailController
{
    /** 1x1 fully transparent PNG, base64-encoded. */
    private const TRANSPARENT_PIXEL = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    /**
     * @param string $fallback One of: placeholder | source | none.
     */
    public function __construct(
        private readonly ConverterResolver $resolver,
        private readonly EngineInterface $engine,
        private readonly ?string $placeholder,
        private readonly string $fallback,
        private readonly string $cacheControl,
    ) {
    }

    public function __invoke(Request $request, string $converter): Response
    {
        $src = (string) $request->query->get('src', '');

        // No source to convert: go straight to the fallback (there is nothing to redirect to).
        if ('' === $src) {
            return $this->fallback(null);
        }

        try {
            $path = $this->engine->thumbnail($src, $this->resolver->resolve($converter));
        } catch (ConverterNotFoundException
            | ImageConvertException
            | UnsupportedImageTypeException
            | ForbiddenSourceException
            | CreateTmpFileException
            | UnknownSourceImageException) {
            return $this->fallback($src);
        }

        $response = (new ConvertImageOutput($path))->getBinaryFileResponse();
        $response->headers->set('Cache-Control', $this->cacheControl);

        return $response;
    }

    /**
     * Produces a graceful response when the thumbnail could not be generated.
     */
    private function fallback(?string $src): Response
    {
        switch ($this->fallback) {
            case 'placeholder':
                return $this->servePlaceholderOrPixel();

            case 'source':
                // Only redirect to a safe http/https source (reuses the SSRF scheme guard).
                // A local path / empty / non-URL source cannot be a redirect target → degrade.
                if (null !== $src && ImageFactory::detectUrl($src)) {
                    return new RedirectResponse($src, Response::HTTP_FOUND);
                }

                return $this->servePlaceholderOrPixel();

            case 'none':
            default:
                return $this->transparentPixel();
        }
    }

    /**
     * Serves the configured placeholder image, falling back to the transparent pixel
     * when no readable placeholder is configured.
     */
    private function servePlaceholderOrPixel(): Response
    {
        if (null !== $this->placeholder && is_file($this->placeholder)) {
            $response = (new ConvertImageOutput($this->placeholder))->getBinaryFileResponse();
            $response->headers->set('Cache-Control', $this->cacheControl);

            return $response;
        }

        return $this->transparentPixel();
    }

    /**
     * A 1x1 transparent PNG (HTTP 200). Marked `no-store` so a transient failure is never
     * cached over a thumbnail that may succeed on a later request.
     */
    private function transparentPixel(): Response
    {
        return new Response(
            (string) base64_decode(self::TRANSPARENT_PIXEL, true),
            Response::HTTP_OK,
            ['Content-Type' => 'image/png', 'Cache-Control' => 'no-store'],
        );
    }
}
