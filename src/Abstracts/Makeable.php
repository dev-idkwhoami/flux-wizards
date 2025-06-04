<?php

namespace Idkwhoami\FluxWizards\Abstracts;

use Closure;
use ReflectionProperty;

abstract class Makeable
{

    /**
     * @param  string  $name
     */
    protected final function __construct(
        protected string $name,
    ) {
    }

    public function boot(): void
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
    public final static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return static
     */
    protected final function fill(array $properties): static
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                continue;
            }

            $reflection = new ReflectionProperty($this, $property);
            $type = $reflection->getType();

            if ($type && $type->getName() === Closure::class && is_string($value)) {
                $value = unserialize($value)->getClosure();
            }

            $this->{$property} = $value;
        }

        $this->filled();

        return $this;
    }

    public final function getName(): string
    {
        return $this->name;
    }

}
