<?php

namespace MrAuGir\Thumbnail\Converter;

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
     * Returns the command to run as an argv array (binary + arguments) so it
     * can be executed through Process without a shell.
     *
     * @param Image $image
     * @return string[]
     */
    public function getCommand(Image $image) : array;

    /**
     * @return string|null
     */
    public function getId() : ?string;
}