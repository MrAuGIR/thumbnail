<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Tests\Kernel\ThumbnailTestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * End-to-end check that the `thumbnail_serve` route reaches ServeThumbnailController and that a
 * failed generation degrades gracefully — never a 500.
 *
 * The kernel is booted and driven directly (not via WebTestCase): this repo ships no phpunit.xml
 * / KERNEL_CLASS, so the implicit kernel discovery WebTestCase relies on is unavailable here.
 * An unknown converter makes ConverterResolver throw before any binary/network is touched, so the
 * test is deterministic without ImageMagick or connectivity.
 */
class ServeThumbnailFunctionalTest extends TestCase
{
    public function testUnknownConverterRedirectsToHttpSourceInsteadOf500(): void
    {
        $kernel = $this->boot();
        $response = $kernel->handle(Request::create('/thumbnail/serve/unknown?src=https://example.com/a.jpg'));

        $this->assertNotSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        // fallback: source + an http source → redirect to the original.
        $this->assertTrue(
            $response->isRedirect('https://example.com/a.jpg'),
            sprintf('Expected a redirect to the source, got %d', $response->getStatusCode()),
        );

        $kernel->shutdown();
    }

    public function testEmptySourceDegradesToPlaceholderInsteadOf500(): void
    {
        $kernel = $this->boot();
        $response = $kernel->handle(Request::create('/thumbnail/serve/convert_vignette?src='));

        // 'source' mode with no URL to redirect to → degrades to the configured placeholder image.
        $this->assertNotSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), sprintf('Expected 2xx, got %d', $response->getStatusCode()));

        $kernel->shutdown();
    }

    private function boot(): ThumbnailTestKernel
    {
        $kernel = new ThumbnailTestKernel('test', true);
        $kernel->boot();

        return $kernel;
    }
}
