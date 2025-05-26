<?php

namespace Idkwhoami\FluxWizards\Core;

class Step
{
    /**
     * The step's unique key.
     *
     * @var string
     */
    protected $key;

    /**
     * The step's display name.
     *
     * @var string
     */
    protected $name;

    /**
     * The validation rules for this step.
     *
     * @var array<string, string|array>
     */
    protected $rules = [];

    /**
     * The fields governed by this step (for reference).
     *
     * @var array<string>
     */
    protected $fields = [];

    /**
     * Create a new step instance.
     *
     * @param string $key
     * @param string $name
     * @param array<string, string|array> $rules
     * @param array<string> $fields
     */
    public function __construct($key, $name = null, array $rules = [], array $fields = [])
    {
        $this->key = $key;
        $this->name = $name !== null ? $name : $key;
        $this->rules = $rules;
        $this->fields = $fields;
    }

    /**
     * Create a new step instance.
     *
     * @param string $key
     * @return static
     */
    public static function make($key)
    {
        return new static($key);
    }

    /**
     * Set the step's name.
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the validation rules for this step.
     *
     * @param array<string, string|array> $rules
     * @return $this
     */
    public function rules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Set the fields governed by this step.
     *
     * @param array<string> $fields
     * @return $this
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get the step's key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the step's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the validation rules for this step.
     *
     * @return array<string, string|array>
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Get the prefixed validation rules for this step.
     *
     * @return array<string, string|array>
     */
    public function getPrefixedRules()
    {
        $prefixedRules = [];

        foreach ($this->rules as $field => $rule) {
            $prefixedRules["{$this->key}.{$field}"] = $rule;
        }

        return $prefixedRules;
    }

    /**
     * Get the fields governed by this step.
     *
     * @return array<string>
     */
    public function getFields()
    {
        return $this->fields;
    }
}
