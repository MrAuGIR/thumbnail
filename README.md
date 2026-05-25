

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
    public function customMethodChain(Request $request, ConverterChain $printThumbnail, Engine $engine, ImageFactory $imageFactory) : JsonResponse {
        $body = $request->toArray();
        if (empty($path = $body['path'])) {
            throw new InvalidArgumentException("image path not found");
        }
        $image = $imageFactory->create($path);

        try {
            foreach ($printThumbnail as $converter) {
                $engine->processConvertion($image, $converter);
            }
        } finally {
            $imageFactory->cleanup($image); // removes the temp file for URL sources
        }

        return new JsonResponse();
    }
```

# Work In Progress ConverterHandler
### URL de resolution thumbnail
```php

class TestController extends AbstractController
{
    public function __construct(
        private readonly ConverterResolver $converterResolver,
        private readonly Engine            $engine,
        private readonly ImageFactory      $imageFactory
    ){}
    
     /**
     * @throws CreateTmpFileException
     * @throws UnknowSourceImageException
     * @throws ImageConvertException
     */
    #[Route("/thumbnail/call/{converter}/{path}", name: "mraugir_thumbnail_converter", requirements: ["path" => ".+" ], methods: ["GET"])]
    public function thumbnailAction(Request $request, string $converter, string $path) : Response {

        $image = $this->imageFactory->create($path);
        $converter = $this->converterResolver->resolve($converter);

        try {
            $outputPath = $this->engine->processConvertion($image,$converter);
        } finally {
            $this->imageFactory->cleanup($image);
        }

        return new BinaryFileResponse($outputPath,200, ['Content-Type' => "image/jpeg"]);
    }
```

### TODO LIST

1. Utiliser la class Extension du bundle pour injecter les paramètres sur les path du projet, des fichiers temporaires
2. injecter ces paramètres sur un service de gestion de fichiers qui sera utiliser ensuite par les services de conversions
3. permettre la conversion dynamique (passer un parametrage en post)
4. gérer le cache pour ne pas re-générer le thumbnail à chaque fois