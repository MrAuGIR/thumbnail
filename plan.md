# Bundle Thumbnail — Retour d'intégration & plan d'évolution

> Rédigé après la **1ʳᵉ intégration réelle** du bundle dans un vrai projet
> (application *Biblio*, Symfony 7.4 : redimensionnement + cache local des couvertures
> de livres OpenLibrary à la place du hotlink). Ce document liste ce qui a bien marché,
> les points à corriger (par sévérité) et des idées d'évolution. Il servira de base
> quand on retravaillera le bundle.

## Contexte de l'intégration

- Install via dépôt VCS (`repositories: [{type: vcs, url: github.com/MrAuGIR/thumbnail}]`) + `composer require mraugir/thumbnail:^1.0`, puis déclaration dans `config/bundles.php`. **OK**, mais il a fallu relâcher la contrainte `symfony/process` (`6.3.*` → `^6.4 || ^7.0`) côté bundle pour passer sur Symfony 7.
- Config d'un converter `cover` dans `config/packages/thumbnail.yaml` (resize 200px, jpeg).
- Côté projet, on a dû **écrire une couche par-dessus le bundle** (service `CoverThumbnailer` + contrôleur + fonction Twig) pour combler des manques : **cache stable**, **garde anti-SSRF**, **fallback**, **stockage inscriptible**. Ces ajouts pointent exactement les axes d'amélioration ci-dessous.

## ✅ Ce qui marche bien (à garder)

- Séparation claire des responsabilités : `Engine` (exécution) / `Converter` (commande) / `Configuration`+`Option` (modèle) / `ImageFactory` (source) / `Resolver` (lookup).
- Config tree (`Configuration` DI) + factories de définitions (`*DefinitionFactory`) : approche propre pour générer un service par converter depuis le YAML.
- `ImageFactory` qui détecte URL vs chemin absolu (enum `Source`) et **télécharge** une URL distante → permet d'attaquer une couverture distante directement. Pratique.
- Support des **chains** de converters.
- Une **vraie suite de tests** (`Tests/` : Engine, Configuration, Converter, Image, Extension, WebTestCase pour les routes). Très bonne base.

---

## 🔴 Sécurité (à traiter en priorité)

### S1 — Injection shell / options non échappées
`Model\Configuration::getOtionsChain()` échappe l'input et l'output (`escapeshellarg`) **mais pas** le nom/valeur des options, et `Engine::processConvertion()` exécute via `Process::fromShellCommandline()` (donc un shell `/bin/sh -c`).

Conséquences vécues : impossible de mettre une géométrie ImageMagick `200x300>` en config (le `>` est interprété comme une **redirection shell**). Plus grave : si une valeur d'option provenait un jour d'une entrée externe → **injection de commande**.

**Fix recommandé : passer `Process` en mode tableau (argv), sans shell ni échappement.**
```php
// Converter::commandToExecute() devrait renvoyer un array argv, pas une string :
public function getCommand(Image $image): array
{
    $cmd = [$this->binaryName, $image->getPath()];
    foreach ($this->configuration->getOptions() as $opt) {
        $cmd[] = $opt->getName();
        if (null !== $opt->getValue()) {
            $cmd[] = $opt->getValue();
        }
    }
    $cmd[] = $this->configuration->getOutputFullPath($image);
    return $cmd;
}

// Engine :
$process = new Process($converter->getCommand($image));   // pas de shell, pas d'escape
$process->mustRun();
```
Bénéfice : plus aucun souci de métacaractère (`>`, espaces, `;`, `$()`…), et on peut documenter des géométries normales.

### S2 — SSRF / LFI via `ImageFactory` + route ouverte
- `ImageFactory::detectUrl()` utilise `FILTER_VALIDATE_URL`, qui accepte `file://`, `ftp://`, `http://169.254.169.254/…`, etc. Puis `ImageFileManager::createResource()` fait `file_get_contents($url)` **sans restriction**.
- La route fournie `Resources/config/routes.yaml` (`/thumbnail/call/{converter}/{path}` avec `path: .+`) passe le `path` **brut** à `ImageFactory::create()`. Si on importe cette route telle quelle, on expose un **endpoint SSRF/LFI non authentifié** (lecture de fichiers locaux / requêtes vers l'infra interne).

C'est précisément pour ça que dans Biblio on n'a **pas** importé cette route et qu'on a écrit notre propre contrôleur avec **whitelist d'hôte** (`covers.openlibrary.org`).

**Fixes :**
- Restreindre les schémas acceptés à `http`/`https` (et rejeter `file://`).
- Ne **pas** livrer une route de conversion ouverte par défaut ; si on la garde, la documenter comme « à protéger » (auth + whitelist d'hôtes/chemins), ou la déplacer dans un exemple.
- Côté fetch distant : utiliser `symfony/http-client` (timeout, taille max, suivi de redirections contrôlé) plutôt que `file_get_contents`.

---

## 🟠 Fonctionnel / Robustesse

### F1 — Aucun cache (le cœur du besoin manque)
Pour une source **URL**, le nom de sortie dérive du fichier temp **aléatoire** (`getOutputFullPath()` = `outputPath + prefix + image.getFileName() + ext`, et `getFileName()` = basename du `tempnam` aléatoire). Résultat : **régénération à chaque appel** + fichiers de sortie aux noms aléatoires. Le TODO du README le reconnaît.

Dans Biblio on a dû ajouter une couche de cache (clé `sha1(url)`, renommage de la sortie, court-circuit si le fichier existe). **Ce serait la fonctionnalité n°1 à internaliser dans le bundle** :
- clé de cache déterministe = hash de **(source + binaire + options)** pour invalider quand la conf change ;
- court-circuit si le rendu existe déjà ;
- API du type `Engine::thumbnail(string $source, Converter $c): string` qui renvoie un chemin stable et caché.

### F2 — Fichiers temporaires jamais nettoyés
`ImageFileManager::createResource()` crée un `tempnam('thumb_')` ; `cleaner()` existe **mais n'est jamais appelé**. Chaque conversion d'URL **laisse un fichier dans `/tmp`** (fuite disque). À nettoyer systématiquement (try/finally) une fois la conversion faite.

### F3 — Handler async vide
`Message\Handler\ThumbnailMessageHandler::__invoke()` est **vide** (injecte `Engine` mais ne fait rien). La conversion asynchrone est donc annoncée mais non implémentée. Soit l'implémenter (résoudre le converter via l'id du message + `processConvertion`), soit retirer le `Message/` tant que ce n'est pas prêt.

### F4 — `Content-Type` de sortie codé en dur
`Action\Output\ConvertImageOutput` renvoie toujours `Content-Type: image/png`, alors que les converters produisent souvent du jpeg (cf. `ext: jpeg`). Déduire le type depuis l'extension/MIME réel du fichier produit.

### F5 — Pas de timeout sur le process
`Process` sans `setTimeout()` : une conversion qui bloque fige la requête/worker. Fixer un timeout raisonnable et le rendre configurable.

### F6 — Dossier de sortie non créé / collisions de noms
- `outputPath` doit exister : le bundle ne fait pas le `mkdir`. (Dans Biblio j'ai dû créer le dossier et gérer les permissions web `www-data` vs CLI `root` → on a même dû le sortir de `public/` vers `var/` pour l'inscriptibilité.) Le bundle pourrait créer le dossier (mkdir récursif) au besoin.
- Pour une source **locale**, le nom de sortie = `prefix + nom_source + ext` → deux images différentes de même nom **s'écrasent**. Le hash de cache (F1) résout aussi ça.

---

## 🟡 Qualité de code / Design

- **Logger** : `Engine` instancie un `DummyLogger` en dur et expose `useLogger()` (setter). Préférer l'injection constructeur `LoggerInterface $logger = new NullLogger()` (PSR-3) ; l'autowiring Symfony branchera le vrai logger. (Et `DummyLogger` ⇒ `Psr\Log\NullLogger` qui existe déjà.)
- **Services publics** : `ConverterDefinitionFactory` force `setPublic(true)` sur chaque converter, et `services.yaml` met `Converter\` / `Action\` / `Engine` en `public: true`. Les services devraient être **privés** par défaut (les contrôleurs sont gérés par `controller.service_arguments`). Public = à éviter.
- **Pollution de l'espace des ids de service** : `ThumbnailExtension` fait `setDefinition($id, …)` avec `$id` = la **clé de conf brute** (ex. `cover`). Un id de service aussi générique peut entrer en collision avec d'autres services du projet. Ne garder que l'alias namespacé (`thumbnail.converter.cover`) + le binding par argument.
- **Nommage trompeur** : `Command\ConvertImage`/`ConvertImageCommand`/`ConvertChainCommand` ne sont **ni** des commandes console **ni** des messages CQRS — ce sont des services applicatifs. Renommer (ex. `*Processor`, `*Action`, ou `*Handler`) pour lever l'ambiguïté.
- **Typos d'API publique** (gênant car c'est du contrat) : `getOtionsChain` → `getOptionsChain`, `UnknowSourceImageException` → `UnknownSourceImageException`, `processConvertion` → `processConversion`.
- **`ConverterChain::$chain`** non initialisé (`private array $chain;`) : `getChain()`/itération avant tout `add()` → erreur « typed property must not be accessed before initialization ». Initialiser `= []`.
- **ImageMagick 7** : le binaire historique est `magick` (le `convert` est un alias de compat, parfois absent). Détecter/permettre `magick` comme binaire, ou documenter le prérequis.
- **DIP** : pas d'interface pour `Engine`. Une `EngineInterface` faciliterait le mock/déco (ex. cache).
- **Resolvers en O(n)** : `ConverterResolver`/`ConverterChainResolver` itèrent et comparent `getId()`. Un `ServiceLocator` indexé par id (tag avec clé) serait O(1) et plus idiomatique.
- **`ConfigurationDefinitionFactory::createDefinition($id, …)`** ignore `$id` (paramètre mort).

---

## 📦 Packaging / DX

- `composer.json` :
  - `"type": "library"` → devrait être **`"symfony-bundle"`** (active la bonne intégration Flex/Composer).
  - `"minimum-stability": "dev"` dans le bundle lui-même : à retirer (laisse ce choix à l'app consommatrice ; ça fragilise la résolution de deps).
  - Pas de champ **`license`** ni email d'auteur → ajouter (ex. MIT) ; nécessaire pour Packagist.
  - PSR-4 mappé sur la **racine** du package (`"MrAuGir\\Thumbnail\\": ""`) : peu conventionnel. Migrer vers une structure **`src/`** (`"MrAuGir\\Thumbnail\\": "src/"`) clarifie et évite l'`exclude-from-classmap` des `Tests/`.
- **Pas de recette Flex** : l'install demande un `bundles.php` + un `thumbnail.yaml` + (éventuellement) les routes à la main. Une recette (ou au moins une section README « copier-coller ») fluidifierait l'install. La contrainte `symfony/process` a aussi dû être élargie pour Symfony 7 — penser au support des versions LTS courantes.
- **README** : encore en mode WIP (sections « Work In Progress », TODO). Manque le **cas d'usage réel** qu'on a utilisé (résolution via `ConverterResolver` + `Engine::processConvertion`, ou via le service `*Command`). Documenter l'API stable.
- Pas publié sur **Packagist** (install VCS only) — OK pour un bundle perso, à garder en tête si diffusion plus large.

---

## ✅ Tests (déjà présents — à étoffer)

La suite existe et couvre l'essentiel (Engine, Configuration, Converter, Image, Extension, routes via WebTestCase). Cas à ajouter pour blinder les points ci-dessus :
- échappement / argv : une option avec espace ou métacaractère ne doit **pas** casser ni injecter ;
- source URL en échec (404, timeout) → exception propre, pas de fatal ;
- nettoyage du fichier temp après conversion (F2) ;
- cache : 2ᵉ appel = pas de régénération (F1) ;
- `BinaryConverter::support()` : rejet d'un MIME non image (actuellement `support()` n'est jamais appelé par l'`Engine` avant conversion — à brancher).

---

## 🚀 Idées d'évolution (roadmap)

1. **Cache de premier niveau intégré** (F1) : API `thumbnail(source, converter): string` qui hash (source+conf), court-circuite si présent, nettoie le temp (F2). C'est l'attente n°1 d'un consommateur.
2. **`Process` en argv + timeout** (S1/F5) : sécurité + robustesse d'un coup.
3. **Driver alternatif sans shell-out** : un converter basé sur l'extension PHP **`ext-imagick`** ou **`gd`** (pas de binaire externe, pas de souci shell ni d'install ImageMagick CLI). Le shell-out resterait un driver parmi d'autres derrière `Converter`.
4. **Fonction/filtre Twig livré par le bundle** : on a dû écrire `cover_thumb()` côté projet. Un `{{ thumbnail(path, 'cover') }}` (filtre) fourni par le bundle serait la vraie valeur ajoutée DX.
5. **Async réellement implémenté** (F3) : générer en tâche de fond (worker Messenger), utile pour des lots de couvertures.
6. **Gestion d'erreurs et fallback** : exposer un comportement « si conversion KO → renvoyer l'original / un placeholder » (on a dû le coder côté projet).
7. **Restructuration `src/` + recette Flex + `type: symfony-bundle` + license** (packaging) pour une install « zéro friction ».

---

## Annexe — ce qu'on a dû construire au-dessus du bundle (Biblio)

Ces briques pourraient remonter dans le bundle :
- `CoverThumbnailer` : cache stable (`cover_<sha1>.jpg`), renommage de la sortie aléatoire, nettoyage du temp, `mkdir` du dossier.
- `CoverController` : route same-origin avec **whitelist d'hôte anti-SSRF**, `Cache-Control` long, **fallback** redirect vers la source si la génération échoue.
- `CoverExtension` : fonction Twig `cover_thumb(url)` (pass-through si URL vide/étrangère).
- Stockage déplacé en `var/thumbnails/` (inscriptible par le serveur web), dossier ouvert en 0777 (partage web/CLI).
- En config : options **sans métacaractère** (`-thumbnail 200x`, pas de `>`) à cause de S1.


## Rules
1. Toujours des messages de commit court.
2. Prefixé les messages de commits [hotfix] [feature] [doc] 