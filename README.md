

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
```yaml
# config/routes/mraugir_thumbnail.yaml

_mraugir_thumbnail:
    resource: "@ThumbnailBundle/Resources/config/routes.yaml"
```

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
        $image = ImageFactory::create($path);

        foreach ($printThumbnail as $converter) {
            $engine->processConvertion($image, $converter);
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
        private readonly Engine            $engine
    ){}
    
     /**
     * @throws CreateTmpFileException
     * @throws UnknowSourceImageException
     * @throws ImageConvertException
     */
    #[Route("/thumbnail/call/{converter}/{path}", name: "mraugir_thumbnail_converter", requirements: ["path" => ".+" ], methods: ["GET"])]
    public function thumbnailAction(Request $request, string $converter, string $path) : Response {

        $image = ImageFactory::create($path);
        $converter = $this->converterResolver->resolve($converter);

        $outputPath = $this->engine->processConvertion($image,$converter);

        return new BinaryFileResponse($outputPath,200, ['Content-Type' => "image/jpeg"]);
    }
```

### TODO LIST

1. Utiliser la class Extension du bundle pour injecter les paramètres sur les path du projet, des fichiers temporaires
2. injecter ces paramètres sur un service de gestion de fichiers qui sera utiliser ensuite par les services de conversions
3. permettre la conversion dynamique (passer un parametrage en post)
4. gérer le cache pour ne pas re-générer le thumbnail à chaque fois