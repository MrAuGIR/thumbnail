<?php

namespace MrAuGir\Thumbnail\Model;

class Option
{
    public function __construct(protected string  $name, protected ?string $value = null)
    {
    }

    /**
     * @return string
     */
    public function getLineOption() :  string {
        return sprintf("%s %s",$this->name, $this->value ?? '');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Option
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Option
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }


}