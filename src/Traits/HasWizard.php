<?php

namespace Idkwhoami\FluxWizards\Traits;

use Idkwhoami\FluxWizards\Core\Wizard;
use Illuminate\Contracts\View\View;
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
     * Initialize the component.
     *
     * @return void
     */
    public function mountHasWizard(): void
    {
        $this->wizard = $this->createWizard();
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
        $this->wizard->nextStep();
    }

    /**
     * Move to the previous step.
     *
     * @return void
     */
    public function previousStep(): void
    {
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
        $this->wizard->setCurrentStepByKey($stepKey);
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
