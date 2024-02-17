<?php

namespace MrAuGir\Thumbnail\Model;

class Configuration
{
    /**
     * @param Option[] $options
     */
    public function __construct(protected array $options = [])
    {
    }

    /**
     * @return string
     */
    public function getOtionsChain() : string {
        $return = "";
        foreach ($this->options as $option) {
            $return .= $option->getLineOption().' ';
        }
        return trim($return);
    }

    /**
     * @param Option $option
     * @return $this
     */
    public function addOption(Option $option) : self {
        $this->options[] = $option;
        return $this;
    }
}