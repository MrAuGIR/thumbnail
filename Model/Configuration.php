<?php

namespace MrAuGir\Thumbnail\Model;

class Configuration
{
    protected const PREFIX = "thumb_";

    protected const EXT = "jpg";
    /**
     * @param Option[] $options
     */
    public function __construct(protected array $options = [], protected ?string $outputPath = null)
    {
    }

    /**
     * @param Image $image
     * @return string
     */
    public function getOtionsChain(Image $image) : string {
        $return = "";
        foreach ($this->options as $option) {
            $return .= $option->getLineOption().' ';
        }
        return  sprintf("%s %s %s",
            escapeshellarg($image->getPath()),
            trim($return),
            $this->getOutputFullPath($image)
        );
    }

    /**
     * @param Option $option
     * @return $this
     */
    public function addOption(Option $option) : self {
        $this->options[] = $option;
        return $this;
    }

    /**
     * @param string $outputPath
     * @return $this
     */
    public function setOutputPath(string $outputPath) : self {
        $this->outputPath = $outputPath;
        return $this;
    }

    /**
     * @param Image $image
     * @return string
     */
    public function getOutputFullPath(Image $image) : string {
        $path = $this->outputPath.self::PREFIX.$image->getFileName().".".self::EXT;
        return escapeshellarg($path);
    }
}