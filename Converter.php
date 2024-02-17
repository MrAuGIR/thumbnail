<?php

namespace MrAuGir\Thumbnail;

use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Image;

interface Converter
{
    /**
     * @param Image $image
     * @return bool
     */
    public function support(Image $image) : bool;

    /**
     * @param Configuration $configuration
     * @return self
     */
    public function setConfiguration(Configuration $configuration) : self;

    /**
     * @return string
     */
    public function commandToExecute() : string;
}