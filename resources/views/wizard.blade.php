@php
    /** @var \Idkwhoami\FluxWizards\Concretes\Wizard $wizard */
    $currentStep = $wizard->getCurrent();
@endphp
@dump($this->wizard)
<div class="flex flex-col w-full space-y-6 p-2">
    @if($currentStep)
        <flux:heading>{{ $currentStep->getLabel() }}</flux:heading>

        {{-- Include the current step's view --}}
        <div class="px-2 py-6">
            @include(config('flux-wizards.steps_directory') . '.' . $currentStep->getView())
        </div>

        <div class="flex w-full">
            @if(!$currentStep->isFirst())
                <flux:button variant="filled" wire:click="previousStep">
                    {{ __('flux-wizards::wizard.back') }}
                </flux:button>
            @endif
            <flux:spacer/>
            <flux:button icon:trailing="arrow-right" variant="primary" wire:click="nextStep">
                @if($currentStep->isLast())
                    {{ __('flux-wizards::wizard.finish') }}
                @else
                    {{ __('flux-wizards::wizard.next') }}
                @endif
            </flux:button>
        </div>
    @else
        <flux:callout icon="circle-x" variant="danger">
            No steps defined for this wizard.
        </flux:callout>
    @endif
</div>
