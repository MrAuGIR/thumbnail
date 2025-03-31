<?php

namespace MrAuGir\Thumbnail\Factory;

use MrAuGir\Thumbnail\Model\Option;

class OptionFactory
{
    /**
     * @param array $option
     * @return Option
     */
    public static function create(array $option) : Option {
        if (!isset($option['name'])) {
            throw new \InvalidArgumentException("array option passed to the method is invalid");
        }
        return new Option($option['name'],$option['value']);
    }
}