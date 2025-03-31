<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Converter\BinaryConverter;
use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Logger\DummyLogger;
use MrAuGir\Thumbnail\Model\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class Engine
{
    protected ?LoggerInterface $logger;

    public function __construct(
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
        $this->logger->info(sprintf("commande %s", $converter->commandToExecute($image)));
        $process = Process::fromShellCommandline($converter->commandToExecute($image));
        $process->run();

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