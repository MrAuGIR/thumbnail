# Thumbnail Bundle

*[English](README.en.md) · [Français](README.md)*

A small Symfony bundle to generate thumbnails by shelling out to an image binary
(ImageMagick by default). It provides a deterministic, cached `Engine`, a YAML-driven
declaration of *converters* (and *chains* of converters), and argument-name autowiring
so a converter can be injected straight into your services.

## Requirements

- PHP **>= 8.2**
- Symfony **6.4** or **7.x**
- An image **CLI binary** reachable on the server (ImageMagick by default).

### ImageMagick (7+)

The bundle runs an external binary; by default the converter `binary` is `convert`.

> **ImageMagick 7 note.** The historical `convert` command is now a *legacy alias* of
> `magick` and is **absent on some installs** (or prints a deprecation warning). If
> `convert` is not available, set the binary explicitly in your converter config:
>
> ```yaml
> thumbnail:
>     converters:
>         cover:
>             binary: "magick"   # ImageMagick 7 entry point
>             # ...
> ```

`binary` is resolved by `Process`, so you can also point it at an absolute path
(e.g. `/usr/bin/magick`) or another tool (e.g. `gm` for GraphicsMagick).

Install ImageMagick: <https://imagemagick.org> — Ubuntu: <https://doc.ubuntu-fr.org/imagemagick>.

## Installation

> No Flex recipe is published, so the steps below are **manual**.

### 1. Require the package

The bundle is **not on Packagist**. Add its repository to your application's
`composer.json`:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/MrAuGIR/thumbnail" }
    ]
}
```

then require it:

```bash
composer require mraugir/thumbnail:^2.0
```

### 2. Register the bundle

```php
<?php
// config/bundles.php
return [
    // ...
    MrAuGir\Thumbnail\ThumbnailBundle::class => ['all' => true],
];
```

### 3. Declare at least one converter

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    converters:
        cover:
            binary: "convert"        # use "magick" on ImageMagick 7 (see Requirements)
            configuration:
                prefix: "thumb_"
                ext: "jpeg"
                options:
                    - { name: "-resize", value: "200x" }
                outputPath: "%kernel.project_dir%/var/thumbnails/"
```

> `outputPath` must be **writable** by the web *and* CLI user. Prefer a path under
> `var/` over `public/` to avoid permission clashes, and serve the files through a
> controller (see *Examples*). The directory is created automatically if missing.

### 4. (Optional) import the example routes

> ⚠️ **Security** — The routes shipped in `Resources/config/routes.yaml` are
> **unauthenticated** and accept an arbitrary `{path}`. Do **not** import them on a
> public app without protecting them (firewall + `allowed_hosts`, see below). They
> are intended as an example; prefer wiring your own protected controller.

```yaml
# config/routes/mraugir_thumbnail.yaml
_mraugir_thumbnail:
    resource: "@ThumbnailBundle/Resources/config/routes.yaml"
```

> 💡 To **display** thumbnails, prefer the ready-made, fallback-protected
> **`thumbnail_serve`** route instead of these raw examples — see
> *[Serving thumbnails](#serving-thumbnails-the-thumbnail_serve-route)* below.

## Security configuration

Remote sources are restricted to `http`/`https`; `file://`, `ftp://`, `phar://`, …
are rejected (no LFI/SSRF via stream wrappers). Tune the policy under the
`thumbnail` key:

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    # Hosts allowed as remote sources. Empty = any host (scheme is still enforced).
    allowed_hosts:
        - 'covers.openlibrary.org'
    fetch_timeout: 10          # seconds — network timeout when downloading a source
    max_file_size: 10485760    # bytes  — reject oversized remote payloads (default 10 MiB)
    process_timeout: 60        # seconds — kill a conversion that runs too long
    converters:
        # ...
```

Conversions run through `Process` in **array mode** (no shell), so ImageMagick
geometries such as `200x300>` can be used verbatim in `options` without being
interpreted as a shell redirection.

## Configuring converters

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    converters:
        convert_vignette:
            binary: "convert"
            configuration:
                prefix: "thumb_240x24_"     # output file name prefix
                ext: "jpeg"                 # output extension (also drives the response Content-Type)
                options:                    # passed verbatim as argv to the binary
                    - { name: "-resize", value: "240x24" }
                outputPath: "%kernel.project_dir%/public/assets/thumbnail/"
```

Each declared converter is registered as a service and bound to its **argument name**
(`convert_vignette` → `Converter $convertVignette`), so you can inject it directly:

```php
use MrAuGir\Thumbnail\Converter\Converter;

#[Route("/my/custom/url", name: "my_custom_url", methods: ["GET"])]
public function customMethod(Converter $convertVignette): JsonResponse
{
    // $convertVignette is the "convert_vignette" converter
    return new JsonResponse();
}
```

### Chains

A *chain* applies several converters to the same source.

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    converters:
        convert_mignature:
            binary: "convert"
            configuration:
                prefix: "thumb_240x24_"
                ext: "jpeg"
                options:
                    - { name: "-resize", value: "240x24" }
                outputPath: "%kernel.project_dir%/public/assets/thumbnail/"
        # convert_screen_shot: ...
    chains:
        print_thumbnail:
            - 'convert_mignature'
            - 'convert_screen_shot'
```

A chain is bound the same way (`print_thumbnail` → `ConverterChain $printThumbnail`):

```php
use MrAuGir\Thumbnail\Converter\ConverterChain;

#[Route("/my/converters/chain", name: "my_converters_chain", methods: ["GET"])]
public function customMethodChain(ConverterChain $printThumbnail): JsonResponse
{
    return new JsonResponse();
}
```

## Serving thumbnails: the `thumbnail_serve` route

For the common case — show a cached thumbnail in an `<img>` — the bundle ships a
ready-to-use controller on the named route **`thumbnail_serve`**. Unlike the raw example
routes, it **never returns a 500 when generation fails**: it degrades to a fallback
(placeholder image, redirect to the original source, or a transparent pixel).

Import it (separate from the example `routes.yaml`, so you only expose the safe endpoint):

```yaml
# config/routes/mraugir_thumbnail.yaml
_thumbnail_serve:
    resource: "@ThumbnailBundle/Resources/config/routes/serve.yaml"
```

This adds `GET /thumbnail/serve/{converter}?src=<source>`. The `converter` is a path
segment; the **source is the `src` query parameter** (so a full URL survives
percent-encoding instead of tripping over encoded slashes). On success it returns the
cached image with the configured `Cache-Control`; on a caught error it applies the
fallback below.

> ⚠️ The source is still fetched server-side, so set `allowed_hosts` (see
> *Security configuration*) to the hosts you trust. The `http`/`https` scheme is always
> enforced.

### Fallback configuration

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    # Image served when generation fails and fallback = placeholder (optional, absolute path).
    placeholder: "%kernel.project_dir%/public/img/placeholder.png"
    # placeholder | source | none   (default: source)
    fallback: source
    # Cache-Control set on a successfully served thumbnail (and on the placeholder).
    cache_control: "public, max-age=31536000, immutable"
    converters:
        # ...
```

| `fallback`    | On failure the controller… |
|---------------|----------------------------|
| `source`      | redirects (302) to the original source **if** it is an `http`/`https` URL; otherwise degrades to `placeholder`. |
| `placeholder` | serves the configured `placeholder` image; if none is set/readable, degrades to a transparent pixel. |
| `none`        | returns a 1×1 transparent PNG (HTTP 200). |

An empty source goes straight to the fallback. Failures and redirects are sent with
`Cache-Control: no-store`, so a transient error is never cached over a thumbnail that
may succeed later. The `Engine` itself keeps throwing — the fallback is a
presentation-layer decision made by the controller, not the core.

## Twig: the `thumbnail()` function

Twig is an **optional** dependency. When `symfony/twig-bundle` is installed, the bundle
registers a `thumbnail()` function that returns a same-origin URL to the `thumbnail_serve`
route above — so you never have to hand-write a Twig extension or leak a disk path:

```twig
<img src="{{ thumbnail(book.coverUrl, 'cover') }}" alt="cover">
```

`thumbnail(source, converter)` builds `/thumbnail/serve/<converter>?src=<source>`. It
always returns a usable URL — even for an empty source, in which case the controller
serves the placeholder/pixel per your `fallback` config. It requires the
`thumbnail_serve` route to be imported (above) and an `allowed_hosts` policy.

Install the optional dependency:

```bash
composer require symfony/twig-bundle
```

## Stable API

The bundle exposes a small, stable surface. Type-hint the **interfaces/services** below;
they are autowired.

### `EngineInterface` (service `MrAuGir\Thumbnail\EngineInterface`)

```php
// Generate (or reuse the cache for) one thumbnail; returns a stable output path.
public function thumbnail(string $source, Converter $converter): string;

// Same, for every converter of a chain; the source is downloaded at most once.
public function thumbnailAll(string $source, iterable $converters): iterable; // <string>

// Low-level: convert an already-resolved Image. Prefer thumbnail()/thumbnailAll().
public function processConversion(Image $image, Converter $converter): string;
```

`$source` is either a local **absolute path** or an `http`/`https` **URL**.

### Resolving by id at runtime

When the converter/chain id is only known at runtime (e.g. from a route parameter),
resolve it via the locators:

```php
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterChainResolver;

$converter = $converterResolver->resolve('convert_vignette');   // O(1) lookup
$chain     = $chainResolver->resolve('print_thumbnail');
// Both throw MrAuGir\Thumbnail\Exception\ConverterNotFoundException if unknown.
```

### Caching behaviour

`EngineInterface::thumbnail()` returns a stable, cached output path. The file name is
`prefix + <hash> . ext`, where the hash derives from **source + binary + options + ext**, so:

- the **same** source and config always resolve to the **same** file;
- a **cache hit short-circuits**: no download, no conversion — the existing path is returned;
- changing the converter config (resize, quality, binary, …) yields a new file;
- the output directory is created if missing, and temp files downloaded from URLs are cleaned up automatically.

### Exceptions

All live in `MrAuGir\Thumbnail\Exception\`:
`UnknownSourceImageException`, `ForbiddenSourceException`, `CreateTmpFileException`,
`UnsupportedImageTypeException`, `ImageConvertException`, `ConverterNotFoundException`.

## Examples

### A single thumbnail, resolved from the URL

```php
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\EngineInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ThumbnailController extends AbstractController
{
    public function __construct(
        private readonly ConverterResolver $converterResolver,
        private readonly EngineInterface   $engine,
    ) {}

    /**
     * @throws \MrAuGir\Thumbnail\Exception\CreateTmpFileException
     * @throws \MrAuGir\Thumbnail\Exception\UnknownSourceImageException
     * @throws \MrAuGir\Thumbnail\Exception\ImageConvertException
     */
    #[Route("/thumbnail/{converter}/{path}", requirements: ["path" => ".+"], methods: ["GET"])]
    public function __invoke(string $converter, string $path): BinaryFileResponse
    {
        $converter  = $this->converterResolver->resolve($converter);
        $outputPath = $this->engine->thumbnail($path, $converter); // cached

        // Content-Type is derived from the produced file.
        return new BinaryFileResponse($outputPath);
    }
}
```

### A chain

```php
use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\EngineInterface;

#[Route("/my/converters/chain", methods: ["POST"])]
public function customMethodChain(Request $request, ConverterChain $printThumbnail, EngineInterface $engine): JsonResponse
{
    $body = $request->toArray();
    if (empty($path = $body['path'] ?? null)) {
        throw new \InvalidArgumentException("image path not found");
    }

    // Downloads the source at most once, caches each render, cleans the temp file.
    $paths = iterator_to_array($engine->thumbnailAll($path, $printThumbnail));

    return new JsonResponse(['path' => $paths]);
}
```

## Asynchronous generation (application-side)

The bundle deliberately ships **no** Messenger message/handler: when, how, and what to
do with the generated path (queue, retries, store in DB, …) is your application's
concern. `EngineInterface::thumbnail()` is the synchronous primitive you call from a
worker — it is cache-aware and cleans its own temp files, so it is safe to enqueue.

```php
// src/Message/GenerateThumbnail.php  (in your app)
final class GenerateThumbnail
{
    public function __construct(
        public readonly string $source,
        public readonly string $converter,
    ) {}
}
```

```php
// src/MessageHandler/GenerateThumbnailHandler.php  (in your app)
use App\Message\GenerateThumbnail;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\EngineInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GenerateThumbnailHandler
{
    public function __construct(
        private readonly EngineInterface   $engine,
        private readonly ConverterResolver $converterResolver,
    ) {}

    public function __invoke(GenerateThumbnail $message): void
    {
        $converter = $this->converterResolver->resolve($message->converter);
        $this->engine->thumbnail($message->source, $converter); // cached + temp cleaned
    }
}
```

```php
// Dispatch from anywhere (controller, command, …)
$bus->dispatch(new GenerateThumbnail($url, 'convert_vignette'));
```

## TODO

1. ~~Use the bundle Extension to inject parameters (paths, temp files).~~ ✅
2. ~~Inject those parameters into a file-management service used by the converters.~~ ✅
3. ~~Cache thumbnails to avoid regenerating on every call.~~ ✅
4. Dynamic conversion (pass converter settings at request time, e.g. via POST).
