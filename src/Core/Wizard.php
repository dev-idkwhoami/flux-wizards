<?php

namespace Idkwhoami\FluxWizards\Core;

use Closure;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Wireable;

class Wizard implements Wireable
{
    /**
     * The wizard name.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * The steps in this wizard.
     *
     * @var array<string, Step>
     */
    protected array $steps = [];

    /**
     * The current step key.
     *
     * @var string|null
     */
    protected ?string $currentStep = null;

    /**
     * The wizard data.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Custom step transitions.
     *
     * @var array<string, array<string, Closure>>
     */
    protected array $transitions = [];

    /**
     * The flow callback.
     *
     * @var Closure|null
     */
    protected Closure|null $flowCallback = null;

    /**
     * @param  string|null  $currentStep
     * @param  array  $data
     * @param  Closure|null  $flowCallback
     * @param  string|null  $name
     * @param  Step[]  $steps
     * @param  \Closure[][]  $transitions
     */
    public function __construct(
        ?string $name,
        array $steps = [],
        array $transitions = [],
        ?string $currentStep = null,
        array $data = [],
        ?Closure $flowCallback = null,
    ) {
        $this->name = $name;
        $this->steps = $steps;
        $this->transitions = $transitions;
        $this->currentStep = $currentStep;
        $this->data = $data;
        $this->flowCallback = $flowCallback;
    }

    /**
     * Create a new wizard instance.
     *
     * @param  array  $data  Initial wizard data
     * @return static
     */
    public static function make(string $name, array $data = []): static
    {
        return new static($name, data: $data);
    }

    /**
     * Set the wizard name.
     *
     * @param  string  $name
     * @return $this
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Add multiple steps to the wizard.
     *
     * @param  array<Step>  $steps
     * @return $this
     */
    public function steps(array $steps): static
    {
        foreach ($steps as $step) {
            $this->addStep($step);
        }

        return $this;
    }

    /**
     * Set the flow callback.
     *
     * @param  Closure  $callback
     * @return $this
     */
    public function flow(Closure $callback): static
    {
        $this->flowCallback = $callback;

        return $this;
    }

    /**
     * Get the wizard name.
     *
     * @return string|null
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Add a step to the wizard.
     *
     * @param  Step  $step
     * @return $this
     */
    public function addStep(Step $step): static
    {
        $this->steps[$step->getKey()] = $step;

        // Set the first step as the current step if not already set
        if ($this->currentStep === null) {
            $this->currentStep = $step->getKey();
        }

        return $this;
    }

    /**
     * Get all steps.
     *
     * @return array<string, Step>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Get a step by key.
     *
     * @param  string  $key
     * @return Step
     * @throws InvalidArgumentException
     */
    public function getStep($key): Step
    {
        if (!isset($this->steps[$key])) {
            throw new InvalidArgumentException("Step '{$key}' not found.");
        }

        return $this->steps[$key];
    }

    /**
     * Get the current step.
     *
     * @return Step|null
     */
    public function getCurrentStep(): ?Step
    {
        if ($this->currentStep === null) {
            return null;
        }

        return $this->getStep($this->currentStep);
    }

    /**
     * Set the current step.
     *
     * @param  string  $key
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCurrentStep($key): static
    {
        if (!isset($this->steps[$key])) {
            throw new InvalidArgumentException("Step '{$key}' not found.");
        }

        $this->currentStep = $key;

        return $this;
    }

    /**
     * Get the current step key.
     *
     * @return string|null
     */
    public function getCurrentStepKey(): string
    {
        return $this->currentStep;
    }

    /**
     * Get the validation rules for the current step.
     *
     * @return array
     */
    public function getCurrentStepRules(): array
    {
        if ($this->currentStep === null) {
            return [];
        }

        return $this->getStep($this->currentStep)->getPrefixedRules();
    }

    /**
     * Get the wizard data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the wizard data.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Update the wizard data.
     *
     * @param  array  $data
     * @return $this
     */
    public function updateData(array $data): static
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Get the data for a specific step.
     *
     * @param  string  $stepKey
     * @return array
     */
    public function getStepData($stepKey): array
    {
        return isset($this->data[$stepKey]) ? $this->data[$stepKey] : [];
    }

    /**
     * Set the data for a specific step.
     *
     * @param  string  $stepKey
     * @param  array  $data
     * @return $this
     */
    public function setStepData($stepKey, array $data): static
    {
        $this->data[$stepKey] = $data;

        return $this;
    }

    /**
     * Update the data for a specific step.
     *
     * @param  string  $stepKey
     * @param  array  $data
     * @return $this
     */
    public function updateStepData($stepKey, array $data): static
    {
        if (!isset($this->data[$stepKey])) {
            $this->data[$stepKey] = [];
        }

        $this->data[$stepKey] = array_merge($this->data[$stepKey], $data);

        return $this;
    }

    /**
     * Add a transition between steps.
     *
     * @param  string  $fromStep
     * @param  string  $toStep
     * @param  Closure  $condition
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addTransition($fromStep, $toStep, Closure $condition): static
    {
        if (!isset($this->steps[$fromStep])) {
            throw new InvalidArgumentException("Step '{$fromStep}' not found.");
        }

        if (!isset($this->steps[$toStep])) {
            throw new InvalidArgumentException("Step '{$toStep}' not found.");
        }

        if (!isset($this->transitions[$fromStep])) {
            $this->transitions[$fromStep] = [];
        }

        $this->transitions[$fromStep][$toStep] = $condition;

        return $this;
    }

    /**
     * Get the next step key.
     *
     * @return string|null
     */
    public function getNextStepKey(): ?string
    {
        if ($this->currentStep === null) {
            return null;
        }

        // Check if a flow callback is set
        if ($this->flowCallback !== null) {
            $callback = $this->flowCallback;
            $nextStep = $callback($this->currentStep, $this->data);
            if ($nextStep !== null && isset($this->steps[$nextStep])) {
                return $nextStep;
            }
        }

        // Check for custom transitions
        if (isset($this->transitions[$this->currentStep])) {
            foreach ($this->transitions[$this->currentStep] as $toStep => $condition) {
                if ($condition($this->data)) {
                    return $toStep;
                }
            }
        }

        // Default to the next step in the sequence
        $keys = array_keys($this->steps);
        $currentIndex = array_search($this->currentStep, $keys);

        if ($currentIndex !== false && $currentIndex < count($keys) - 1) {
            return $keys[$currentIndex + 1];
        }

        return null;
    }

    /**
     * Get the previous step key.
     *
     * @return string|null
     */
    public function getPreviousStepKey(): ?string
    {
        if ($this->currentStep === null) {
            return null;
        }

        $keys = array_keys($this->steps);
        $currentIndex = array_search($this->currentStep, $keys);

        if ($currentIndex !== false && $currentIndex > 0) {
            return $keys[$currentIndex - 1];
        }

        return null;
    }

    /**
     * Move to the next step.
     *
     * @return bool Whether the step was changed
     */
    public function nextStep(): bool
    {
        $nextStepKey = $this->getNextStepKey();

        if ($nextStepKey !== null) {
            $this->currentStep = $nextStepKey;
            return true;
        }

        return false;
    }

    /**
     * Move to the previous step.
     *
     * @return bool Whether the step was changed
     */
    public function previousStep(): bool
    {
        $previousStepKey = $this->getPreviousStepKey();

        if ($previousStepKey !== null) {
            $this->currentStep = $previousStepKey;
            return true;
        }

        return false;
    }

    /**
     * Check if the wizard is on the first step.
     *
     * @return bool
     */
    public function isFirstStep(): bool
    {
        if ($this->currentStep === null) {
            return false;
        }

        $keys = array_keys($this->steps);
        return $this->currentStep === reset($keys);
    }

    /**
     * Check if the wizard is on the last step.
     *
     * @return bool
     */
    public function isLastStep(): bool
    {
        if ($this->currentStep === null) {
            return false;
        }

        $keys = array_keys($this->steps);
        return $this->currentStep === end($keys);
    }

    final protected static function serializeTransitions(array $data): array
    {
        $transitions = [];

        foreach ($data as $stepKey => $transition) {
            foreach ($transition as $transitionKey => $condition) {
                $transitions[] = [$stepKey, $transitionKey, serialize(new SerializableClosure($condition))];
            }
        }

        return $transitions;
    }

    final protected static function unserializeTransitions(array $data): array
    {
        $transitions = [];

        foreach ($data as $stepKey => $transition) {
            foreach ($transition as $transitionKey => $condition) {
                $transitions[$stepKey][$transitionKey] = unserialize(
                    $condition
                )->getClosure();
            }
        }

        return $transitions;
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'steps' => $this->steps,
            'transitions' => static::serializeTransitions($this->transitions),
            'currentStep' => $this->currentStep,
            'data' => $this->data,
            'flowCallback' => serialize(new SerializableClosure($this->flowCallback)),
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['name'],
            $value['steps'],
            static::unserializeTransitions($value['transitions']),
            $value['currentStep'],
            $value['data'],
            unserialize($value['flowCallback'])->getClosure()
        );
    }
}
