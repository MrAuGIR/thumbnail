<?php

namespace MrAuGir\Thumbnail\Converter;

use MrAuGir\Thumbnail\Model\Image;

class BinaryConverter implements Converter
{
    use TraitConfiguration, TraitIdentifier;

    public string $binaryName = "convert";

    private static array $allowedMimeType = [
        'image/gif',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];

    /**
     * @param string $binaryName
     * @param string|null $id
     */
    public function __construct(string $binaryName = 'convert',string $id = null)
    {
        $this->binaryName = $binaryName;
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function support(Image $image): bool
    {
        return in_array($image->getTypeMime(),self::$allowedMimeType);
    }

    /**
     * @inheritDoc
     */
    public function getCommand(Image $image): array
    {
        $outputPath = $this->getOutputPathForSource($image->getSourceId());

        return array_merge([$this->binaryName], $this->configuration->getCommandArguments($image, $outputPath));
    }

    /**
     * @inheritDoc
     */
    public function getOutputPathForSource(string $source): string
    {
        $config = $this->configuration;

        // Cache key = source + binary + options + ext: changing the conversion
        // config (resize, quality, binary, …) yields a new file; the same inputs
        // always map to the same path (deterministic, no random temp name).
        $parts = [$source, $this->binaryName, $config->getPrefix(), $config->getExt()];
        foreach ($config->getOptions() as $option) {
            $parts[] = implode(' ', $option->getArguments());
        }

        $key = substr(hash('sha256', implode("\0", $parts)), 0, 32);

        return $config->getOutputPath() . $config->getPrefix() . $key . '.' . $config->getExt();
    }
}