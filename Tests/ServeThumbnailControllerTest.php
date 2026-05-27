<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Action\ServeThumbnailController;
use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\EngineInterface;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deterministic unit tests for the fallback behaviour (no kernel/network/imagemagick needed).
 * The Engine keeps throwing; these assert the controller turns each failure into a graceful
 * response — never a 500.
 */
class ServeThumbnailControllerTest extends TestCase
{
    private const PLACEHOLDER = __DIR__.'/images/test.jpg';
    private const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    public function testSuccessReturnsFileWithLongCacheControl(): void
    {
        $controller = new ServeThumbnailController(
            $this->resolverReturning($this->createMock(Converter::class)),
            $this->engineReturning(self::PLACEHOLDER),
            null,
            'none',
            self::CACHE_CONTROL,
        );

        $response = $controller(self::request('https://example.com/a.jpg'), 'cover');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(self::CACHE_CONTROL, $response->headers->get('Cache-Control'));
    }

    public function testSourceFallbackRedirectsToHttpSource(): void
    {
        $controller = $this->controller('source', null);

        $response = $controller(self::request('https://example.com/a.jpg'), 'unknown');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com/a.jpg', $response->getTargetUrl());
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    public function testSourceFallbackDegradesToPlaceholderWhenSourceNotRedirectable(): void
    {
        // 'source' mode but the src is a local path (not http/https) → cannot redirect → placeholder.
        $controller = $this->controller('source', self::PLACEHOLDER);

        $response = $controller(self::request('/var/data/local.jpg'), 'unknown');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame(self::CACHE_CONTROL, $response->headers->get('Cache-Control'));
    }

    public function testPlaceholderFallbackServesConfiguredImage(): void
    {
        $controller = $this->controller('placeholder', self::PLACEHOLDER);

        $response = $controller(self::request('https://example.com/a.jpg'), 'unknown');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame(self::CACHE_CONTROL, $response->headers->get('Cache-Control'));
    }

    public function testPlaceholderFallbackDegradesToPixelWhenNoPlaceholder(): void
    {
        $controller = $this->controller('placeholder', null);

        $response = $controller(self::request('https://example.com/a.jpg'), 'unknown');

        $this->assertPixel($response);
    }

    public function testNoneFallbackReturnsTransparentPixel(): void
    {
        $controller = $this->controller('none', self::PLACEHOLDER);

        $response = $controller(self::request('https://example.com/a.jpg'), 'unknown');

        $this->assertPixel($response);
    }

    public function testEmptySourceUsesFallbackWithoutTouchingTheEngine(): void
    {
        // Empty src short-circuits to the fallback; with 'source' mode and no URL it degrades.
        $controller = $this->controller('source', self::PLACEHOLDER);

        $response = $controller(self::request(''), 'cover');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testConversionFailureIsCaughtAndNeverThrows(): void
    {
        $resolver = $this->createMock(ConverterResolver::class);
        $resolver->method('resolve')->willReturn($this->createMock(Converter::class));
        $engine = $this->createMock(EngineInterface::class);
        $engine->method('thumbnail')->willThrowException(new ImageConvertException('boom'));

        $controller = new ServeThumbnailController($resolver, $engine, null, 'none', self::CACHE_CONTROL);

        $response = $controller(self::request('https://example.com/a.jpg'), 'cover');

        $this->assertPixel($response);
    }

    private function assertPixel(Response $response): void
    {
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame('no-store', $response->headers->get('Cache-Control'));
        $this->assertNotSame('', (string) $response->getContent());
    }

    private function controller(string $fallback, ?string $placeholder): ServeThumbnailController
    {
        return new ServeThumbnailController(
            $this->resolverThrowing(),
            $this->createMock(EngineInterface::class),
            $placeholder,
            $fallback,
            self::CACHE_CONTROL,
        );
    }

    private function resolverThrowing(): ConverterResolver
    {
        $resolver = $this->createMock(ConverterResolver::class);
        $resolver->method('resolve')->willThrowException(new ConverterNotFoundException('unknown'));

        return $resolver;
    }

    private function resolverReturning(Converter $converter): ConverterResolver
    {
        $resolver = $this->createMock(ConverterResolver::class);
        $resolver->method('resolve')->willReturn($converter);

        return $resolver;
    }

    private function engineReturning(string $path): EngineInterface
    {
        $engine = $this->createMock(EngineInterface::class);
        $engine->method('thumbnail')->willReturn($path);

        return $engine;
    }

    private static function request(string $src): Request
    {
        return Request::create('/thumbnail/serve/cover', 'GET', '' === $src ? [] : ['src' => $src]);
    }
}
