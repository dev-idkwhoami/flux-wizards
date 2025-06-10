<?php

namespace Idkwhoami\FluxWizards\Tests;

use Idkwhoami\FluxWizards\Traits\HasWizard;
use Livewire\Component;

class ExampleWizard extends Component
{
    use HasWizard;

    protected function initialStep(): string
    {
        return 'account';
    }

    protected function steps(): array
    {
        return [
            'account',
            'profile',
            'billing',
        ];
    }

    /*
     * To progress forward / backwards use functions next / previous
     *
     */

    public function computeNextStep(string $next): string
    {
        /* logic to determine next step or $next is next step in the steps array */
        return $next;
    }

    public function computePreviousStep(string $previous): string
    {
        /* logic to determine previous step o4 $next is previous step in the steps array */
        return $previous;
    }
}
