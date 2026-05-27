# Thumbnail Bundle

*[English](README.en.md) · [Français](README.md)*

Un petit bundle Symfony pour générer des miniatures en déléguant à un binaire image
(ImageMagick par défaut). Il fournit un `Engine` déterministe et mis en cache, une
déclaration des *converters* (et des *chaînes* de converters) pilotée par YAML, et
l'autowiring par nom d'argument pour injecter un converter directement dans tes services.

## Prérequis

- PHP **>= 8.2**
- Symfony **6.4** ou **7.x**
- Un **binaire image en ligne de commande** accessible sur le serveur (ImageMagick par défaut).

### ImageMagick (7+)

Le bundle exécute un binaire externe ; par défaut, le `binary` du converter est `convert`.

> **Note ImageMagick 7.** La commande historique `convert` est désormais un *alias legacy* de
> `magick` et est **absente sur certaines installations** (ou affiche un avertissement de
> dépréciation). Si `convert` n'est pas disponible, définis explicitement le binaire dans la
> config de ton converter :
>
> ```yaml
> thumbnail:
>     converters:
>         cover:
>             binary: "magick"   # point d'entrée ImageMagick 7
>             # ...
> ```

`binary` est résolu par `Process` : tu peux donc aussi pointer vers un chemin absolu
(ex. `/usr/bin/magick`) ou un autre outil (ex. `gm` pour GraphicsMagick).

Installer ImageMagick : <https://imagemagick.org> — Ubuntu : <https://doc.ubuntu-fr.org/imagemagick>.

## Installation

> Aucune recette Flex n'est publiée : les étapes ci-dessous sont **manuelles**.

### 1. Installer le package

Le bundle **n'est pas sur Packagist**. Ajoute son dépôt dans le `composer.json` de ton
application :

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/MrAuGIR/thumbnail" }
    ]
}
```

puis installe-le :

```bash
composer require mraugir/thumbnail:^2.0
```

### 2. Enregistrer le bundle

```php
<?php
// config/bundles.php
return [
    // ...
    MrAuGir\Thumbnail\ThumbnailBundle::class => ['all' => true],
];
```

### 3. Déclarer au moins un converter

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    converters:
        cover:
            binary: "convert"        # utilise "magick" avec ImageMagick 7 (voir Prérequis)
            configuration:
                prefix: "thumb_"
                ext: "jpeg"
                options:
                    - { name: "-resize", value: "200x" }
                outputPath: "%kernel.project_dir%/var/thumbnails/"
```

> `outputPath` doit être **accessible en écriture** par l'utilisateur web *et* CLI. Préfère un
> chemin sous `var/` plutôt que `public/` pour éviter les conflits de permissions, et sers les
> fichiers via un contrôleur (voir *Exemples*). Le dossier est créé automatiquement s'il
> n'existe pas.

### 4. (Optionnel) importer les routes d'exemple

> ⚠️ **Sécurité** — Les routes livrées dans `Resources/config/routes.yaml` sont
> **non authentifiées** et acceptent un `{path}` arbitraire. Ne les importe **pas** sur une app
> publique sans les protéger (firewall + `allowed_hosts`, voir plus bas). Elles sont fournies à
> titre d'exemple ; préfère brancher ton propre contrôleur protégé.

```yaml
# config/routes/mraugir_thumbnail.yaml
_mraugir_thumbnail:
    resource: "@ThumbnailBundle/Resources/config/routes.yaml"
```

> 💡 Pour **afficher** des miniatures, préfère la route **`thumbnail_serve`** prête à l'emploi
> et protégée par un fallback, plutôt que ces exemples bruts — voir
> *[Servir les miniatures](#servir-les-miniatures-avec-la-route-thumbnail_serve)* ci-dessous.

## Configuration de la sécurité

Les sources distantes sont restreintes à `http`/`https` ; `file://`, `ftp://`, `phar://`, …
sont rejetées (pas de LFI/SSRF via les wrappers de flux). Ajuste la politique sous la clé
`thumbnail` :

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    # Hôtes autorisés comme sources distantes. Vide = tout hôte (le schéma reste imposé).
    allowed_hosts:
        - 'covers.openlibrary.org'
    fetch_timeout: 10          # secondes — timeout réseau au téléchargement d'une source
    max_file_size: 10485760    # octets  — rejette les payloads distants trop gros (défaut 10 Mio)
    process_timeout: 60        # secondes — tue une conversion qui tourne trop longtemps
    converters:
        # ...
```

Les conversions passent par `Process` en **mode tableau** (sans shell) : les géométries
ImageMagick comme `200x300>` peuvent donc être utilisées telles quelles dans `options` sans
être interprétées comme une redirection shell.

## Configurer les converters

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    converters:
        convert_vignette:
            binary: "convert"
            configuration:
                prefix: "thumb_240x24_"     # préfixe du nom de fichier de sortie
                ext: "jpeg"                 # extension de sortie (détermine aussi le Content-Type de la réponse)
                options:                    # passées telles quelles en argv au binaire
                    - { name: "-resize", value: "240x24" }
                outputPath: "%kernel.project_dir%/public/assets/thumbnail/"
```

Chaque converter déclaré est enregistré comme service et lié à son **nom d'argument**
(`convert_vignette` → `Converter $convertVignette`), ce qui permet de l'injecter directement :

```php
use MrAuGir\Thumbnail\Converter\Converter;

#[Route("/my/custom/url", name: "my_custom_url", methods: ["GET"])]
public function customMethod(Converter $convertVignette): JsonResponse
{
    // $convertVignette est le converter "convert_vignette"
    return new JsonResponse();
}
```

### Chaînes

Une *chaîne* applique plusieurs converters à la même source.

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

Une chaîne est liée de la même façon (`print_thumbnail` → `ConverterChain $printThumbnail`) :

```php
use MrAuGir\Thumbnail\Converter\ConverterChain;

#[Route("/my/converters/chain", name: "my_converters_chain", methods: ["GET"])]
public function customMethodChain(ConverterChain $printThumbnail): JsonResponse
{
    return new JsonResponse();
}
```

## Servir les miniatures avec la route `thumbnail_serve`

Pour le cas courant — afficher une miniature mise en cache dans une balise `<img>` — le bundle
fournit un contrôleur prêt à l'emploi sur la route nommée **`thumbnail_serve`**. Contrairement
aux routes d'exemple brutes, elle **ne renvoie jamais d'erreur 500 quand la génération
échoue** : elle se rabat sur un fallback (image placeholder, redirection vers la source
d'origine, ou un pixel transparent).

Importe-la (séparée de l'exemple `routes.yaml`, pour n'exposer que l'endpoint sûr) :

```yaml
# config/routes/mraugir_thumbnail.yaml
_thumbnail_serve:
    resource: "@ThumbnailBundle/Resources/config/routes/serve.yaml"
```

Cela ajoute `GET /thumbnail/serve/{converter}?src=<source>`. Le `converter` est un segment de
chemin ; la **source est le paramètre de requête `src`** (ainsi une URL complète survit à
l'encodage en pourcent au lieu de buter sur les slashs encodés). En cas de succès, elle renvoie
l'image en cache avec le `Cache-Control` configuré ; sur une erreur interceptée, elle applique
le fallback ci-dessous.

> ⚠️ La source est tout de même récupérée côté serveur : définis donc `allowed_hosts` (voir
> *Configuration de la sécurité*) avec les hôtes de confiance. Le schéma `http`/`https` est
> toujours imposé.

### Configuration du fallback

```yaml
# config/packages/thumbnail.yaml
thumbnail:
    # Image servie quand la génération échoue et que fallback = placeholder (optionnel, chemin absolu).
    placeholder: "%kernel.project_dir%/public/img/placeholder.png"
    # placeholder | source | none   (défaut : source)
    fallback: source
    # Cache-Control posé sur une miniature servie avec succès (et sur le placeholder).
    cache_control: "public, max-age=31536000, immutable"
    converters:
        # ...
```

| `fallback`    | En cas d'échec, le contrôleur… |
|---------------|--------------------------------|
| `source`      | redirige (302) vers la source d'origine **si** c'est une URL `http`/`https` ; sinon se rabat sur `placeholder`. |
| `placeholder` | sert l'image `placeholder` configurée ; si aucune n'est définie/lisible, se rabat sur un pixel transparent. |
| `none`        | renvoie un PNG transparent 1×1 (HTTP 200). |

Une source vide va directement au fallback. Les échecs et redirections sont envoyés avec
`Cache-Control: no-store`, pour qu'une erreur transitoire ne soit jamais mise en cache à la
place d'une miniature qui pourrait réussir plus tard. L'`Engine` lui-même continue de lever des
exceptions — le fallback est une décision de la couche présentation prise par le contrôleur, pas
par le cœur.

## Twig : la fonction `thumbnail()`

Twig est une dépendance **optionnelle**. Quand `symfony/twig-bundle` est installé, le bundle
enregistre une fonction `thumbnail()` qui renvoie une URL same-origin vers la route
`thumbnail_serve` ci-dessus — tu n'as donc jamais à écrire une extension Twig à la main ni à
exposer un chemin disque :

```twig
<img src="{{ thumbnail(book.coverUrl, 'cover') }}" alt="cover">
```

`thumbnail(source, converter)` construit `/thumbnail/serve/<converter>?src=<source>`. Elle
renvoie toujours une URL utilisable — même pour une source vide, auquel cas le contrôleur sert
le placeholder/pixel selon ta config `fallback`. Elle nécessite que la route `thumbnail_serve`
soit importée (ci-dessus) et une politique `allowed_hosts`.

Installer la dépendance optionnelle :

```bash
composer require symfony/twig-bundle
```

## API stable

Le bundle expose une petite surface stable. Type-hinte les **interfaces/services** ci-dessous ;
ils sont autowirés.

### `EngineInterface` (service `MrAuGir\Thumbnail\EngineInterface`)

```php
// Génère (ou réutilise le cache pour) une miniature ; renvoie un chemin de sortie stable.
public function thumbnail(string $source, Converter $converter): string;

// Idem, pour chaque converter d'une chaîne ; la source est téléchargée au plus une fois.
public function thumbnailAll(string $source, iterable $converters): iterable; // <string>

// Bas niveau : convertit une Image déjà résolue. Préfère thumbnail()/thumbnailAll().
public function processConversion(Image $image, Converter $converter): string;
```

`$source` est soit un **chemin absolu** local, soit une **URL** `http`/`https`.

### Résolution par id à l'exécution

Quand l'id du converter/de la chaîne n'est connu qu'à l'exécution (ex. depuis un paramètre de
route), résous-le via les locators :

```php
use MrAuGir\Thumbnail\Converter\Resolver\ConverterResolver;
use MrAuGir\Thumbnail\Converter\Resolver\ConverterChainResolver;

$converter = $converterResolver->resolve('convert_vignette');   // lookup O(1)
$chain     = $chainResolver->resolve('print_thumbnail');
// Les deux lèvent MrAuGir\Thumbnail\Exception\ConverterNotFoundException si inconnu.
```

### Comportement du cache

`EngineInterface::thumbnail()` renvoie un chemin de sortie stable et mis en cache. Le nom de
fichier est `prefix + <hash> . ext`, où le hash dérive de **source + binaire + options + ext**,
donc :

- la **même** source et la **même** config résolvent toujours vers le **même** fichier ;
- un **hit de cache court-circuite** : pas de téléchargement, pas de conversion — le chemin existant est renvoyé ;
- changer la config du converter (resize, qualité, binaire, …) produit un nouveau fichier ;
- le dossier de sortie est créé s'il manque, et les fichiers temporaires téléchargés depuis des URLs sont nettoyés automatiquement.

### Exceptions

Toutes dans `MrAuGir\Thumbnail\Exception\` :
`UnknownSourceImageException`, `ForbiddenSourceException`, `CreateTmpFileException`,
`UnsupportedImageTypeException`, `ImageConvertException`, `ConverterNotFoundException`.

## Exemples

### Une miniature unique, résolue depuis l'URL

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
        $outputPath = $this->engine->thumbnail($path, $converter); // en cache

        // Le Content-Type est dérivé du fichier produit.
        return new BinaryFileResponse($outputPath);
    }
}
```

### Une chaîne

```php
use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\EngineInterface;

#[Route("/my/converters/chain", methods: ["POST"])]
public function customMethodChain(Request $request, ConverterChain $printThumbnail, EngineInterface $engine): JsonResponse
{
    $body = $request->toArray();
    if (empty($path = $body['path'] ?? null)) {
        throw new \InvalidArgumentException("chemin de l'image introuvable");
    }

    // Télécharge la source au plus une fois, met chaque rendu en cache, nettoie le fichier temporaire.
    $paths = iterator_to_array($engine->thumbnailAll($path, $printThumbnail));

    return new JsonResponse(['path' => $paths]);
}
```

## Génération asynchrone (côté application)

Le bundle ne livre **délibérément aucun** message/handler Messenger : quand, comment et quoi
faire du chemin généré (file d'attente, retries, stockage en BDD, …) relève de ton application.
`EngineInterface::thumbnail()` est la primitive synchrone que tu appelles depuis un worker —
elle est cache-aware et nettoie ses propres fichiers temporaires, donc elle est sûre à mettre
en file.

```php
// src/Message/GenerateThumbnail.php  (dans ton app)
final class GenerateThumbnail
{
    public function __construct(
        public readonly string $source,
        public readonly string $converter,
    ) {}
}
```

```php
// src/MessageHandler/GenerateThumbnailHandler.php  (dans ton app)
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
        $this->engine->thumbnail($message->source, $converter); // en cache + temp nettoyé
    }
}
```

```php
// Dispatch depuis n'importe où (contrôleur, commande, …)
$bus->dispatch(new GenerateThumbnail($url, 'convert_vignette'));
```

## TODO

1. ~~Utiliser l'Extension du bundle pour injecter les paramètres (chemins, fichiers temporaires).~~ ✅
2. ~~Injecter ces paramètres dans un service de gestion de fichiers utilisé par les converters.~~ ✅
3. ~~Mettre en cache les miniatures pour éviter de régénérer à chaque appel.~~ ✅
4. Conversion dynamique (passer les réglages du converter au moment de la requête, ex. via POST).
