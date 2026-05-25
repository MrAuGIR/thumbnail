

https://doc.ubuntu-fr.org/imagemagick

## Installation

#### Enable

```php
<?php
// config/bundles.php
return [
    ...
    MrAuGir\Thumbnail\ThumbnailBundle::class => ['all' => true]
];

```

#### routing

> ⚠️ **Security** — The routes shipped in `Resources/config/routes.yaml` are
> **unauthenticated** and accept an arbitrary `{path}`. Do **not** import them on a
> public app without protecting them (firewall + `allowed_hosts`, see below). They
> are intended as an example; prefer wiring your own protected controller.

```yaml
# config/routes/mraugir_thumbnail.yaml

_mraugir_thumbnail:
    resource: "@ThumbnailBundle/Resources/config/routes.yaml"
```

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
geometries such as `200x300>` can now be used verbatim in `options` without being
interpreted as a shell redirection.

## Usages
```yaml
# config/packages/thumbnail.yaml
thumbnail:
    converters:
        convert_vignette:
            binary: "convert"
            configuration:
                prefix: "thumb_240x24_"
                ext: "jpeg"
                options:
                    - { name: "-resize", value: "240x24"}
                outputPath: "public/assets/thumbnail/"

```

### In a Controller
```php
    #[Route("/my/custom/url", name: "my_custom_url", methods: [Request::METHOD_GET])]
    public function customMethod(Request $request,Converter $convertVignette) : JsonResponse {
        
        return new JsonResponse();
    }
```


## Converter Chain

```yaml
# Config/packages/thumbnail.yaml
thumbnail:
    converters:
        convert_mignature:
            binary: "convert"
            configuration:
                prefix: "thumb_240x24_"
                ext: "jpeg"
                options:
                    - { name: "-resize", value: "240x24"}
                outputPath: "%kernel.project_dir%/public/assets/thumbnail/"
        ....
        
    chains:
        print_thumbnail:
            - 'convert_mignature'
            - 'convert_screen_shot'
```

### In a Controller

```php
    #[Route("/my/converters/chain", name: "my_converters_chain", methods: [Request::METHOD_GET])]
    public function customMethodChain(Request $request, ConverterChain $printThumbnail) : JsonResponse {

        return new JsonResponse();
    }
```

### Exemple

```php
    #[Route("/my/converters/chain", name: "my_converters_chain", methods: [Request::METHOD_POST])]
    public function customMethodChain(Request $request, ConverterChain $printThumbnail, Engine $engine) : JsonResponse {
        $body = $request->toArray();
        if (empty($path = $body['path'])) {
            throw new InvalidArgumentException("image path not found");
        }

        // Downloads the source at most once, caches each render, cleans the temp file.
        $paths = iterator_to_array($engine->thumbnailAll($path, $printThumbnail));

        return new JsonResponse(['path' => $paths]);
    }
```

## Caching

`Engine::thumbnail(string $source, Converter $converter): string` returns a stable,
cached output path. The file name is `prefix + <hash> . ext`, where the hash derives
from **source + binary + options + ext**, so:

- the **same** source and config always resolve to the **same** file;
- a **cache hit short-circuits**: no download, no conversion — the existing path is returned;
- changing the converter config (resize, quality, binary, …) yields a new file;
- temp files downloaded from URLs are cleaned up automatically.

```php
class TestController extends AbstractController
{
    public function __construct(
        private readonly ConverterResolver $converterResolver,
        private readonly Engine            $engine,
    ){}

    /**
     * @throws CreateTmpFileException
     * @throws UnknowSourceImageException
     * @throws ImageConvertException
     */
    #[Route("/thumbnail/call/{converter}/{path}", name: "mraugir_thumbnail_converter", requirements: ["path" => ".+" ], methods: ["GET"])]
    public function thumbnailAction(Request $request, string $converter, string $path) : Response {

        $converter  = $this->converterResolver->resolve($converter);
        $outputPath = $this->engine->thumbnail($path, $converter); // cached

        return new BinaryFileResponse($outputPath, 200, ['Content-Type' => "image/jpeg"]);
    }
}
```

### TODO LIST

1. ~~Utiliser la class Extension du bundle pour injecter les paramètres (paths, fichiers temporaires)~~ ✅
2. ~~injecter ces paramètres sur un service de gestion de fichiers utilisé par les services de conversion~~ ✅
3. permettre la conversion dynamique (passer un parametrage en post)
4. ~~gérer le cache pour ne pas re-générer le thumbnail à chaque fois~~ ✅