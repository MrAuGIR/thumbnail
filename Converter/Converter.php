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
     * Returns the deterministic output path for the given source, derived from a
     * cache key (source + binary + options + ext). Identical inputs always map to
     * the same path, which enables short-circuiting an already generated thumbnail.
     *
     * @param string $source
     * @return string
     */
    public function getOutputPathForSource(string $source) : string;

    /**
     * @return string|null
     */
    public function getId() : ?string;
}