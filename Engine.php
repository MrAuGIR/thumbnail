<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Logger\DummyLogger;
use MrAuGir\Thumbnail\Model\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Process\Process;

class Engine
{
    protected ?LoggerInterface $logger;

    /**
     * @var Converter[]
     */
    protected iterable $converters;

    public function __construct(
        #[TaggedIterator("mraugir.thumbnail.converter")] iterable $converters
    )
    {
        $this->converters = $converters;
        $this->logger = new DummyLogger();
    }

    /**
     * @throws ImageConvertException
     */
    public function process(Image $image): void
    {
        foreach ($this->converters as $converter) {
            if ($converter->support($image)) {
                $this->processConvertion($image, $converter);
            }
        }
    }

    /**
     * @param Image $image
     * @param Converter $converter
     * @return void
     * @throws ImageConvertException
     */
    public function processConvertion(Image $image, Converter $converter): void
    {
        $this->logger->info(sprintf("commande %s", $converter->commandToExecute($image)));
        $process = Process::fromShellCommandline($converter->commandToExecute($image));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ImageConvertException("Exception while convert image " . $image->getPath() . '-' . $process->getErrorOutput());
        }
    }

    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function useLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param Converter $converter
     * @return $this
     */
    public function addConverter(Converter $converter): self
    {
        $this->converters[] = $converter;
        return $this;
    }
}