<?php

namespace MrAuGir\Thumbnail\Model;

class Configuration
{
    protected string $prefix;

    protected string $ext;

    /**
     * @param Option[] $options
     */
    public function __construct(protected array $options = [], protected ?string $outputPath = null)
    {
        $this->prefix = "thumb_";
        $this->ext = "jpg";
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
            escapeshellarg($this->getOutputFullPath($image))
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
        return $this->outputPath.$this->prefix.$image->getFileName().".".$this->ext;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix) : self {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function setExtension(string $extension) : self {
        $this->ext = $extension;
        return $this;
    }
}