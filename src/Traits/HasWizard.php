<?php

namespace Idkwhoami\FluxWizards\Traits;


use Livewire\Component;

/**
 * @mixin Component
 */
trait HasWizard
{
    public ?string $currentStep = null;

    public function bootHasWizard(): void
    {
        if (!in_array(Component::class, class_parents($this))) {
            throw new \Exception('The trait '.__TRAIT__.' must be used on a class that implements '.Component::class);
        }

        $this->currentStep = $this->initialStep();
    }

    abstract protected function steps(): array;

    abstract protected function initialStep(): string;

    public final function next(): void
    {
        $stepCount = count($this->steps());
        $currentKey = array_find($this->steps(), fn ($step) => $step === $this->currentStep);
        $currentKey += 1;
        $cappedKey = max(0, min($stepCount - 1, $currentKey));

        $this->currentStep = $this->computeNextStep($this->steps()[$cappedKey]);
    }

    public final function previous(): void
    {
        $stepCount = count($this->steps());
        $currentKey = array_find($this->steps(), fn ($step) => $step === $this->currentStep);
        $currentKey -= 1;
        $cappedKey = max(0, min($stepCount - 1, $currentKey));

        $this->currentStep = $this->computePreviousStep($this->steps()[$cappedKey]);
    }

    abstract public function computeNextStep(string $next): string;

    abstract public function computePreviousStep(string $previous): string;
}
