<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Logger\DummyLogger;
use MrAuGir\Thumbnail\Model\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class Engine
{
    protected ?LoggerInterface $logger;

    /**
     * @param int $processTimeout Maximum duration (seconds) a conversion process may run before being killed.
     */
    public function __construct(
        private readonly int $processTimeout = 60,
    )
    {
        $this->logger = new DummyLogger();
    }

    /**
     * @param Image $image
     * @param Converter $converter
     * @return string
     * @throws ImageConvertException
     */
    public function processConvertion(Image $image, Converter $converter): string
    {
        $command = $converter->getCommand($image);
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
        /** @var BinaryConverter $converter */
        return $converter->getConfiguration()->getOutputFullPath($image);
    }

    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function useLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}