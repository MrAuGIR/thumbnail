<?php

namespace MrAuGir\Thumbnail\Converter;

use MrAuGir\Thumbnail\Model\Configuration;
use MrAuGir\Thumbnail\Model\Image;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag("mraugir.thumbnail.converter")]
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
     * @param Image $image
     * @return string
     */
    public function commandToExecute(Image $image) : string;
}