<?php

namespace Idkwhoami\FluxWizards\Traits;

use Idkwhoami\FluxWizards\Concretes\Wizard;
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

    public array $data = [];

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function bootHasWizard(): void
    {
        $this->wizard = $this->createWizard();
        $this->wizard->boot();

        $this->wizard->setData($this->data);
    }

    /*public function updated(string $propertyName, $value): void
    {
        dump($propertyName, $value);
    }*/

    public function updatedData(): void
    {
        $this->wizard->setData($this->data);
    }

    /**
     * Create the wizard instance.
     *
     * @return Wizard
     */
    abstract protected function createWizard(): Wizard;

    abstract protected function complete(array $data): void;

    /**
     * Move to the next step.
     *
     * @return void
     */
    public function nextStep(): void
    {
        if($this->wizard->getCurrent()->isLast()) {
            $this->complete($this->data);
        } else {
            $this->wizard->next();
        }
    }

    /**
     * Move to the previous step.
     *
     * @return void
     */
    public function previousStep(): void
    {
        $this->wizard->previous();
    }

    public function hasErrors(): bool
    {
        return $this->wizard->getRoot()->hasErrors();
    }

    /**
     * Render the wizard component.
     *
     * @return \Illuminate\View\View
     */
    public function renderWizard(): View
    {
        return view('flux-wizards::wizard', [
            'wizard' => $this->wizard,
        ]);
    }

    final public function render(): View
    {
        return $this->renderWizard();
    }
}
