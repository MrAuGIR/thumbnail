

https://doc.ubuntu-fr.org/imagemagick

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