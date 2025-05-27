<?php

namespace Idkwhoami\FluxWizards\Core;

use Livewire\Wireable;

class Wizard implements Wireable
{
    /**
     * The root step of the wizard.
     *
     * @var Step
     */
    protected Step $rootStep;

    /**
     * The current step of the wizard.
     *
     * @var Step
     */
    protected Step $currentStep;

    /**
     * The data collected from all steps.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * The history of visited steps.
     *
     * @var array<string>
     */
    protected array $history = [];

    /**
     * The name of the wizard.
     *
     * @var string
     */
    protected string $name = '';

    /**
     * The flow resolver for the wizard.
     *
     * @var \Closure|null
     */
    protected ?\Closure $flowResolver = null;

    /**
     * Create a new wizard instance.
     *
     * @param Step $rootStep
     */
    public function __construct(Step $rootStep)
    {
        $this->rootStep = $rootStep;
        $this->currentStep = $rootStep;
        $this->history[] = $rootStep->getKey();
    }

    /**
     * Create a new wizard instance.
     *
     * @param array $initialData
     * @return static
     */
    public static function make(array $initialData = []): self
    {
        $rootStep = Step::make('root', 'Root Step', 'steps.root');
        $wizard = new static($rootStep);

        if (!empty($initialData)) {
            $wizard->setData($initialData);
        }

        return $wizard;
    }

    /**
     * Get the root step of the wizard.
     *
     * @return Step
     */
    public function getRootStep(): Step
    {
        return $this->rootStep;
    }

    /**
     * Set the root step of the wizard.
     *
     * @param Step $rootStep
     * @return $this
     */
    public function setRootStep(Step $rootStep): self
    {
        $this->rootStep = $rootStep;

        if ($this->currentStep === null) {
            $this->currentStep = $rootStep;
            $this->history = [$rootStep->getKey()];
        }

        return $this;
    }

    /**
     * Get the current step of the wizard.
     *
     * @return Step
     */
    public function getCurrentStep(): Step
    {
        return $this->currentStep;
    }

    /**
     * Set the current step of the wizard.
     *
     * @param Step $currentStep
     * @return $this
     */
    public function setCurrentStep(Step $currentStep): self
    {
        $this->currentStep = $currentStep;
        $this->history[] = $currentStep->getKey();

        return $this;
    }

    /**
     * Set the current step by key.
     *
     * @param string $key
     * @return $this
     */
    public function setCurrentStepByKey(string $key): self
    {
        $step = $this->findStepByKey($key);

        if ($step) {
            $this->setCurrentStep($step);
        }

        return $this;
    }

    /**
     * Find a step by its key.
     *
     * @param string $key
     * @param Step|null $startStep
     * @return Step|null
     */
    public function findStepByKey(string $key, ?Step $startStep = null): ?Step
    {
        $startStep = $startStep ?? $this->rootStep;

        if ($startStep->getKey() === $key) {
            return $startStep;
        }

        foreach ($startStep->getSteps() as $step) {
            $found = $this->findStepByKey($key, $step);

            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Get the data collected from all steps.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data collected from all steps.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Update the data with new values.
     *
     * @param array $data
     * @return $this
     */
    public function updateData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Get the history of visited steps.
     *
     * @return array<string>
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Set the history of visited steps.
     *
     * @param array<string> $history
     * @return $this
     */
    public function setHistory(array $history): self
    {
        $this->history = $history;
        return $this;
    }

    /**
     * Move to the next step.
     *
     * @return $this
     */
    public function nextStep(): self
    {
        $currentStepChildren = $this->currentStep->getSteps();

        if (empty($currentStepChildren)) {
            // If no children, try to move to the next sibling or parent's next sibling
            $parent = $this->currentStep->getParent();

            if ($parent) {
                $siblings = $parent->getSteps();
                $currentIndex = array_search($this->currentStep, $siblings);

                if ($currentIndex !== false && isset($siblings[$currentIndex + 1])) {
                    // Move to next sibling
                    $this->setCurrentStep($siblings[$currentIndex + 1]);
                } else {
                    // Try to move to parent's next sibling
                    $this->setCurrentStep($parent);
                    $this->nextStep();
                }
            }
        } else {
            // If has children, move to first child
            $nextStep = $this->currentStep->resolveNextStep(
                $this->currentStep,
                $this->data,
                $currentStepChildren[0]
            );

            if ($nextStep) {
                $this->setCurrentStep($nextStep);
            }
        }

        return $this;
    }

    /**
     * Move to the previous step.
     *
     * @return $this
     */
    public function previousStep(): self
    {
        if (count($this->history) > 1) {
            // Remove current step from history
            array_pop($this->history);

            // Get the previous step key
            $previousStepKey = end($this->history);

            // Find and set the previous step
            $previousStep = $this->findStepByKey($previousStepKey);

            if ($previousStep) {
                $this->currentStep = $previousStep;
            }
        }

        return $this;
    }

    /**
     * Check if the wizard is on the first step.
     *
     * @return bool
     */
    public function isFirstStep(): bool
    {
        return $this->currentStep->getKey() === $this->rootStep->getKey();
    }

    /**
     * Check if the wizard is on the last step.
     *
     * @return bool
     */
    public function isLastStep(): bool
    {
        // A step is considered the last step if it has no children
        // or if it's the last child of its parent and the parent has no next sibling
        $currentStepChildren = $this->currentStep->getSteps();

        if (!empty($currentStepChildren)) {
            return false;
        }

        $parent = $this->currentStep->getParent();

        if (!$parent) {
            return true;
        }

        $siblings = $parent->getSteps();
        $currentIndex = array_search($this->currentStep, $siblings);

        return $currentIndex !== false && $currentIndex === count($siblings) - 1;
    }

    /**
     * Get the name of the wizard.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the wizard.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the name of the wizard (alias for setName).
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        return $this->setName($name);
    }

    /**
     * Set the steps of the wizard.
     *
     * @param array<Step> $steps
     * @return $this
     */
    public function steps(array $steps): self
    {
        $this->rootStep->setSteps($steps);
        return $this;
    }

    /**
     * Get the flow resolver for the wizard.
     *
     * @return \Closure|null
     */
    public function getFlowResolver(): ?\Closure
    {
        return $this->flowResolver;
    }

    /**
     * Set the flow resolver for the wizard.
     *
     * @param \Closure $flowResolver
     * @return $this
     */
    public function setFlowResolver(\Closure $flowResolver): self
    {
        $this->flowResolver = $flowResolver;
        return $this;
    }

    /**
     * Set the flow resolver for the wizard (alias for setFlowResolver).
     *
     * @param \Closure $flowResolver
     * @return $this
     */
    public function flow(\Closure $flowResolver): self
    {
        return $this->setFlowResolver($flowResolver);
    }

    /**
     * Convert the wizard to a Livewire-compatible format.
     *
     * @return array
     */
    public function toLivewire()
    {
        return [
            'rootStep' => $this->rootStep->toLivewire(),
            'currentStepKey' => $this->currentStep->getKey(),
            'data' => $this->data,
            'history' => $this->history,
            'name' => $this->name,
        ];
    }

    /**
     * Create a wizard from a Livewire-compatible format.
     *
     * @param array $value
     * @return static
     */
    public static function fromLivewire($value)
    {
        $rootStep = Step::fromLivewire($value['rootStep']);
        $wizard = new static($rootStep);

        $wizard->setData($value['data'] ?? []);
        $wizard->setHistory($value['history'] ?? [$rootStep->getKey()]);

        if (isset($value['name'])) {
            $wizard->setName($value['name']);
        }

        // Set the current step
        if (isset($value['currentStepKey'])) {
            $currentStep = $wizard->findStepByKey($value['currentStepKey']);

            if ($currentStep) {
                $wizard->currentStep = $currentStep;
            }
        }

        return $wizard;
    }
}
