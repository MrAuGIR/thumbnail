<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Exception\ImageConvertException;
use MrAuGir\Thumbnail\Model\Image;
use Symfony\Component\Process\Process;

class Engine
{
    /**
     * @param Image $image
     * @param Converter $converter
     * @return void
     * @throws ImageConvertException
     */
    public function processConvertion(Image $image, Converter $converter) : void {

        $process = Process::fromShellCommandline($converter->commandToExecute($image));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ImageConvertException("Exception while convert image ".$image->getPath());
        }
    }
}