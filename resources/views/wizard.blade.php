<div class="flex flex-col w-full space-y-6 p-2">
    @if($currentStep)
        <flux:heading>{{ $currentStep->getName() }}</flux:heading>

        {{-- Include the current step's view --}}
        <div class="px-2 py-6">
            @include(config('flux-wizards.steps_directory') . '.' . $currentStep->getKey())
        </div>

        <div class="flex w-full">
            @if(!$isFirstStep)
                <flux:button variant="filled" wire:click="previousStep">
                    {{ __('flux-wizards::wizard.back') }}
                </flux:button>
            @endif
            <flux:spacer />
            <flux:button variant="primary" wire:click="nextStep">
                @if($isLastStep)
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
