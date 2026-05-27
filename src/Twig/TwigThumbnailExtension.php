<?php

namespace MrAuGir\Thumbnail\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes {{ thumbnail(source, 'converter') }} so projects no longer have to write their own
 * Twig helper. It returns a same-origin URL to the `thumbnail_serve` route — never a disk path,
 * since the storage usually lives under var/ and is not web-served. The serving controller takes
 * care of generation, caching and the graceful fallback.
 *
 * Optional dependency: this extension is only registered when Twig is installed (see
 * ThumbnailExtension::load()).
 */
class TwigThumbnailExtension extends AbstractExtension
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('thumbnail', $this->thumbnail(...)),
        ];
    }

    /**
     * Builds the serve URL. `converter` is a path segment; `source` is a query parameter (`src`)
     * so a full URL survives percent-encoding without tripping over encoded slashes.
     * An empty source still yields a valid URL — the controller then serves the placeholder/pixel.
     */
    public function thumbnail(string $source, string $converter): string
    {
        return $this->urlGenerator->generate('thumbnail_serve', [
            'converter' => $converter,
            'src' => $source,
        ]);
    }
}
