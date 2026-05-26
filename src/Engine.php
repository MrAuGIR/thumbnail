<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Factory\ImageFactory;
use MrAuGir\Thumbnail\Model\Image;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class Engine implements EngineInterface
{
    /**
     * @param ImageFactory $imageFactory Resolves a source (URL/path) into an Image, downloading remote sources.
     * @param int $processTimeout Maximum duration (seconds) a conversion process may run before being killed.
     * @param LoggerInterface $logger PSR-3 logger; a no-op NullLogger is used until the app wires a real one.
     */
    public function __construct(
        private readonly ImageFactory $imageFactory,
        private readonly int $processTimeout = 60,
        private readonly LoggerInterface $logger = new NullLogger(),
    )
    {
    }

    /**
     * Returns the thumbnail path for a source, generating it only when missing.
     * On a cache hit the source is never downloaded nor converted.
     *
     * @param string $source URL or local path of the original image.
     * @param Converter $converter
     * @return string Deterministic, cached output path.
     * @throws ImageConvertException
     */
    public function thumbnail(string $source, Converter $converter): string
    {
        $outputPath = $converter->getOutputPathForSource($source);
        if (is_file($outputPath)) {
            return $outputPath;
        }

        $image = $this->imageFactory->create($source);
        try {
            return $this->processConversion($image, $converter);
        } finally {
            $this->imageFactory->cleanup($image);
        }
    }

    /**
     * Generates thumbnails for several converters sharing the same source.
     * The source is downloaded at most once (only if at least one render is
     * missing), and each converter is cache-checked individually.
     *
     * @param string $source
     * @param iterable<Converter> $converters
     * @return iterable<string> Output paths, in order.
     * @throws ImageConvertException
     */
    public function thumbnailAll(string $source, iterable $converters): iterable
    {
        $image = null;
        try {
            foreach ($converters as $converter) {
                $outputPath = $converter->getOutputPathForSource($source);
                if (is_file($outputPath)) {
                    yield $outputPath;
                    continue;
                }

                $image ??= $this->imageFactory->create($source);
                yield $this->processConversion($image, $converter);
            }
        } finally {
            if (null !== $image) {
                $this->imageFactory->cleanup($image);
            }
        }
    }

    /**
     * @param Image $image
     * @param Converter $converter
     * @return string
     * @throws ImageConvertException
     */
    public function processConversion(Image $image, Converter $converter): string
    {
        $command = $converter->getCommand($image);
        $outputPath = $converter->getOutputPathForSource($image->getSourceId());

        // Ensure the output directory exists before the binary writes into it.
        $directory = dirname($outputPath);
        if ('' !== $directory && !is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $this->logger->info(sprintf("commande %s", implode(' ', $command)));

        // Array mode (no shell): arguments are passed verbatim, never interpreted by /bin/sh.
        $process = new Process($command);
        $process->setTimeout($this->processTimeout);

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            throw new ImageConvertException(
                sprintf("Conversion of image %s timed out after %ds", $image->getPath(), $this->processTimeout),
                0,
                $e
            );
        }

        if (!$process->isSuccessful()) {
            throw new ImageConvertException("Exception while convert image " . $image->getPath() . '-' . $process->getErrorOutput());
        }

        return $outputPath;
    }
}