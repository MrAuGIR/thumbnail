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
     * The deterministic output path is provided by the converter (cache key).
     *
     * @param Image $image
     * @param string $outputPath
     * @return string[]
     */
    public function getCommandArguments(Image $image, string $outputPath) : array {
        $arguments = [$image->getPath()];

        foreach ($this->options as $option) {
            $arguments = array_merge($arguments, $option->getArguments());
        }

        $arguments[] = $outputPath;

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

    /**
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getExt(): string {
        return $this->ext;
    }

    /**
     * @return string
     */
    public function getOutputPath(): string {
        return (string) $this->outputPath;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array {
        return $this->options;
    }
}