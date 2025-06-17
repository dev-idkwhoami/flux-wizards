<?php

namespace Idkwhoami\FluxWizards\Abstracts;

use Closure;
use ReflectionNamedType;
use ReflectionProperty;

abstract class Makeable
{
    /**
     * @param  string  $name
     */
    final protected function __construct(
        protected string $name,
    ) {
    }

    public function mount(): void
    {
        //
    }

    public function filled(): void
    {
        //
    }

    /**
     * @param  string  $name
     * @return static
     */
    final public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return static
     */
    final protected function fill(array $properties): static
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                continue;
            }

            $reflection = new ReflectionProperty($this, $property);
            $type = $reflection->getType();

            if ($type && $type instanceof ReflectionNamedType && $type->getName() === Closure::class && is_string($value)) {
                $value = unserialize($value)->getClosure();
            }

            $this->{$property} = $value;
        }

        $this->filled();

        return $this;
    }

    final public function getName(): string
    {
        return $this->name;
    }

}
