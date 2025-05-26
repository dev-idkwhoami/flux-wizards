<?php

namespace Idkwhoami\FluxWizards\Traits;

use Idkwhoami\FluxWizards\Core\Wizard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * @mixin Component
 */
trait HasWizard
{
    /**
     * The wizard instance.
     *
     * @var Wizard
     */
    #[Locked] public Wizard $wizard;

    /**
     * The wizard data.
     *
     * @var array
     */
    #[Locked] public array $data = [];

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function mountHasWizard(): void
    {
        $this->wizard = $this->createWizard();
        $this->data = $this->wizard->getData();
    }

    /**
     * Create the wizard instance.
     *
     * @return Wizard
     */
    abstract protected function createWizard(): Wizard;

    /**
     * Move to the next step.
     *
     * @return void
     */
    public function nextStep(): void
    {
        // Validate the current step
        $rules = $this->wizard->getCurrentStepRules();

        if (!empty($rules)) {
            $validator = Validator::make($this->data, $rules);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $messages) {
                    $this->addError($key, $messages[0]);
                }
                return;
            }
        }

        // Update the wizard data
        $this->wizard->setData($this->data);

        // Move to the next step
        $this->wizard->nextStep();
    }

    /**
     * Move to the previous step.
     *
     * @return void
     */
    public function previousStep(): void
    {
        // Update the wizard data
        $this->wizard->setData($this->data);

        // Move to the previous step
        $this->wizard->previousStep();
    }

    /**
     * Go to a specific step.
     *
     * @param string $stepKey
     * @return void
     */
    public function goToStep($stepKey): void
    {
        // Update the wizard data
        $this->wizard->setData($this->data);

        // Go to the specified step
        $this->wizard->setCurrentStep($stepKey);
    }

    /**
     * Get the current step key.
     *
     * @return string|null
     */
    public function getCurrentStepKey(): string
    {
        return $this->wizard->getCurrentStepKey();
    }

    /**
     * Check if the wizard is on the first step.
     *
     * @return bool
     */
    public function isFirstStep(): bool
    {
        return $this->wizard->isFirstStep();
    }

    /**
     * Check if the wizard is on the last step.
     *
     * @return bool
     */
    public function isLastStep(): bool
    {
        return $this->wizard->isLastStep();
    }

    /**
     * Render the wizard component.
     *
     * @return \Illuminate\View\View
     */
    public function renderWizard(): View
    {
        $currentStep = $this->wizard->getCurrentStep();

        return view('flux-wizards::wizard', [
            'currentStep' => $currentStep,
            'isFirstStep' => $this->isFirstStep(),
            'isLastStep' => $this->isLastStep(),
        ]);
    }

    final public function render(): View
    {
        return $this->renderWizard();
    }
}
