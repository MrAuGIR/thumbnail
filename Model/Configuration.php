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
     * Builds the conversion arguments as an argv array (input, options, output)
     * with no shell escaping: the command is run through Process in array mode,
     * so metacharacters such as `>`, spaces or `;` stay inert literal arguments.
     *
     * @param Image $image
     * @return string[]
     */
    public function getCommandArguments(Image $image) : array {
        $arguments = [$image->getPath()];

        foreach ($this->options as $option) {
            $arguments = array_merge($arguments, $option->getArguments());
        }

        $arguments[] = $this->getOutputFullPath($image);

        return $arguments;
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