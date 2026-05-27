<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Twig\TwigThumbnailExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * The Twig function must render a URL to the `thumbnail_serve` route (converter in the path,
 * source as the `src` query parameter), including for an empty source.
 */
class TwigThumbnailExtensionTest extends TestCase
{
    public function testFunctionRendersServeUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with('thumbnail_serve', ['converter' => 'cover', 'src' => 'https://x/y.jpg'])
            ->willReturn('/thumbnail/serve/cover?src=https%3A%2F%2Fx%2Fy.jpg');

        $twig = $this->twig($urlGenerator);

        $this->assertSame(
            '/thumbnail/serve/cover?src=https%3A%2F%2Fx%2Fy.jpg',
            $twig->render('tpl', ['url' => 'https://x/y.jpg']),
        );
    }

    public function testEmptySourceStillBuildsServeUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with('thumbnail_serve', ['converter' => 'cover', 'src' => ''])
            ->willReturn('/thumbnail/serve/cover?src=');

        $twig = $this->twig($urlGenerator);

        $this->assertSame('/thumbnail/serve/cover?src=', $twig->render('tpl', ['url' => '']));
    }

    private function twig(UrlGeneratorInterface $urlGenerator): Environment
    {
        $twig = new Environment(new ArrayLoader(['tpl' => "{{ thumbnail(url, 'cover') }}"]));
        $twig->addExtension(new TwigThumbnailExtension($urlGenerator));

        return $twig;
    }
}
