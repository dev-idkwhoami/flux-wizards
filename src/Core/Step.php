<?php

namespace Idkwhoami\FluxWizards\Core;

use Closure;
use Livewire\Wireable;

class Step implements Wireable
{
    /**
     * The key of the step.
     *
     * @var string
     */
    protected string $key;

    /**
     * The title of the step.
     *
     * @var string
     */
    protected string $title;

    /**
     * The view to render for this step.
     *
     * @var string
     */
    protected string $view;

    /**
     * The child steps of this step.
     *
     * @var array<Step>
     */
    protected array $steps = [];

    /**
     * The closure that determines the next step.
     *
     * @var Closure|null
     */
    protected ?Closure $nextStepResolver = null;

    /**
     * The parent step.
     *
     * @var Step|null
     */
    protected ?Step $parent = null;

    /**
     * The validation rules for the step.
     *
     * @var array
     */
    protected array $rules = [];

    /**
     * The fields for the step.
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * Create a new step instance.
     *
     * @param string $key
     * @param string $title
     * @param string $view
     */
    public function __construct(string $key, string $title, string $view)
    {
        $this->key = $key;
        $this->title = $title;
        $this->view = $view;
    }

    /**
     * Create a new step instance.
     *
     * @param string $key
     * @param string $title = ''
     * @param string $view = ''
     * @return static
     */
    public static function make(string $key, string $title = '', string $view = ''): self
    {
        return new static($key, $title ?: $key, $view ?: "steps.{$key}");
    }

    /**
     * Get the key of the step.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set the key of the step.
     *
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Get the title of the step.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the title of the step.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the title of the step (alias for setTitle).
     *
     * @param string $title
     * @return $this
     */
    public function name(string $title): self
    {
        return $this->setTitle($title);
    }

    /**
     * Set the child steps of this step (alias for setSteps).
     *
     * @param array<Step> $steps
     * @return $this
     */
    public function steps(array $steps): self
    {
        return $this->setSteps($steps);
    }

    /**
     * Get the view of the step.
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Set the view of the step.
     *
     * @param string $view
     * @return $this
     */
    public function setView(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the child steps of this step.
     *
     * @return array<Step>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Set the child steps of this step.
     *
     * @param array<Step> $steps
     * @return $this
     */
    public function setSteps(array $steps): self
    {
        $this->steps = $steps;

        // Set the parent of each child step
        foreach ($steps as $step) {
            $step->setParent($this);
        }

        return $this;
    }

    /**
     * Add a child step.
     *
     * @param Step $step
     * @return $this
     */
    public function addStep(Step $step): self
    {
        $this->steps[] = $step;
        $step->setParent($this);

        return $this;
    }

    /**
     * Get the parent step.
     *
     * @return Step|null
     */
    public function getParent(): ?Step
    {
        return $this->parent;
    }

    /**
     * Set the parent step.
     *
     * @param Step|null $parent
     * @return $this
     */
    public function setParent(?Step $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get the next step resolver.
     *
     * @return Closure|null
     */
    public function getNextStepResolver(): ?Closure
    {
        return $this->nextStepResolver;
    }

    /**
     * Set the next step resolver.
     *
     * @param Closure|null $nextStepResolver
     * @return $this
     */
    public function setNextStepResolver(?Closure $nextStepResolver): self
    {
        $this->nextStepResolver = $nextStepResolver;
        return $this;
    }

    /**
     * Determine the next step based on the current step, data, and default next step.
     *
     * @param Step $currentStep
     * @param array $data
     * @param Step|null $defaultNextStep
     * @return Step|null
     */
    public function resolveNextStep(Step $currentStep, array $data, ?Step $defaultNextStep = null): ?Step
    {
        if ($this->nextStepResolver) {
            return call_user_func($this->nextStepResolver, $currentStep, $data, $defaultNextStep);
        }

        return $defaultNextStep;
    }

    /**
     * Get the validation rules for the step.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set the validation rules for the step.
     *
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Set the validation rules for the step (alias for setRules).
     *
     * @param array $rules
     * @return $this
     */
    public function rules(array $rules): self
    {
        return $this->setRules($rules);
    }

    /**
     * Get the fields for the step.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Set the fields for the step.
     *
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Set the fields for the step (alias for setFields).
     *
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields): self
    {
        return $this->setFields($fields);
    }

    /**
     * Convert the step to a Livewire-compatible format.
     *
     * @return array
     */
    public function toLivewire()
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'view' => $this->view,
            'steps' => array_map(function (Step $step) {
                return $step->toLivewire();
            }, $this->steps),
            'rules' => $this->rules,
            'fields' => $this->fields,
        ];
    }

    /**
     * Create a step from a Livewire-compatible format.
     *
     * @param array $value
     * @return static
     */
    public static function fromLivewire($value)
    {
        $step = new static($value['key'], $value['title'], $value['view']);

        $childSteps = array_map(function ($stepData) {
            return static::fromLivewire($stepData);
        }, $value['steps'] ?? []);

        $step->setSteps($childSteps);

        if (isset($value['rules'])) {
            $step->setRules($value['rules']);
        }

        if (isset($value['fields'])) {
            $step->setFields($value['fields']);
        }

        return $step;
    }
}
