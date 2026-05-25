<?php

namespace MrAuGir\Thumbnail\Model;

class Option
{
    /**
     * @param string $name
     * @param string|null $value
     */
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
     * Returns the option as a list of process arguments (argv), never as a
     * shell string: the value is only added when present so an empty value
     * never becomes a stray empty argument.
     *
     * @return string[]
     */
    public function getArguments(): array
    {
        $arguments = [$this->name];

        if (null !== $this->value && '' !== $this->value) {
            $arguments[] = $this->value;
        }

        return $arguments;
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