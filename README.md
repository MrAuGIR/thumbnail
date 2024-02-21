

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