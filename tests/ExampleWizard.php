<?php

namespace Idkwhoami\FluxWizards\Tests;

use Idkwhoami\FluxWizards\Concretes\Step;
use Idkwhoami\FluxWizards\Concretes\Wizard;
use Idkwhoami\FluxWizards\Traits\HasWizard;
use Livewire\Component;

class ExampleWizard extends Component
{
    use HasWizard;

    protected function createWizard(): Wizard
    {
        return Wizard::make('example')
            ->directory('custom.view.directory')
            ->root(
                Step::make('stepOne')
                    ->rules([
                        //
                    ])
                    ->children([
                        Step::make('stepTwo')
                            ->rules([
                                //
                            ])
                            ->children([
                                Step::make('stepThree')
                                    ->rules([
                                        //
                                    ])
                            ])
                    ])
            );
    }

    protected function complete(array $data): void
    {
        dd($data);
    }
}
