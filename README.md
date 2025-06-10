# Flux Wizards

A Laravel package that provides functionality for building wizard-style multi-step forms with Livewire Flux.

## Features

- Simple, straightforward API for defining wizard steps
- Navigation between steps with next() and previous() methods
- Custom logic for determining step flow
- Blade directives for conditional step rendering
- Livewire integration with the HasWizard trait
- Designed for use with Livewire Flux

## Installation

You can install the package via composer:

```bash
composer require idkwhoami/flux-wizards
```

The package will automatically register its service provider.

## Requirements

- PHP 8.3+
- Laravel 11+
- Livewire Flux 2.1+

## Usage

This package provides a simple way to create multi-step wizards in your Laravel application using Livewire. Below is a comprehensive example of how to use the package.

### Creating a Wizard

To create a wizard, use the `HasWizard` trait in your Livewire component and implement the required methods:

```php
<?php

namespace App\Livewire;

use Idkwhoami\FluxWizards\Traits\HasWizard;
use Livewire\Component;

class UserRegistrationWizard extends Component
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

    public function computeNextStep(string $next): string
    {
        // Custom logic to determine the next step
        // For example, skip billing if user is on a free plan
        if ($next === 'billing' && $this->isFreePlan) {
            return 'confirmation';
        }

        return $next;
    }

    public function computePreviousStep(string $previous): string
    {
        // Custom logic to determine the previous step
        // For example, skip billing if user is on a free plan
        if ($previous === 'billing' && $this->isFreePlan) {
            return 'profile';
        }

        return $previous;
    }

    public function render()
    {
        return view('livewire.user-registration-wizard');
    }
}
```

### Required Methods

When using the `HasWizard` trait, you must implement the following methods:

1. `initialStep()`: Returns the key of the first step in your wizard.
2. `steps()`: Returns an array of step keys in the order they should appear.
3. `computeNextStep(string $next)`: Allows you to customize the next step logic.
4. `computePreviousStep(string $previous)`: Allows you to customize the previous step logic.

### Navigation Methods

The `HasWizard` trait provides two methods for navigating between steps:

1. `next()`: Move to the next step in the sequence.
2. `previous()`: Move to the previous step in the sequence.

### Creating the Wizard View

Create a Blade view for your wizard component. You can use the `@step` and `@end-step` directives to conditionally show content based on the current step:

```blade
<!-- resources/views/livewire/user-registration-wizard.blade.php -->
<div>
    <h1>User Registration</h1>

    <div class="wizard-steps">
        @step('account')
            <h2>Account Information</h2>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" wire:model="email">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" wire:model="password">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>
        @end-step

        @step('profile')
            <h2>Profile Information</h2>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" wire:model="name">
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" wire:model="bio"></textarea>
                @error('bio') <span class="error">{{ $message }}</span> @enderror
            </div>
        @end-step

        @step('billing')
            <h2>Billing Information</h2>
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" wire:model="card_number">
                @error('card_number') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="expiry">Expiry Date</label>
                <input type="text" id="expiry" wire:model="expiry">
                @error('expiry') <span class="error">{{ $message }}</span> @enderror
            </div>
        @end-step

        <div class="wizard-navigation">
            @if($currentStep !== $this->steps()[0])
                <button type="button" wire:click="previous" class="btn btn-secondary">
                    Previous
                </button>
            @endif

            <button type="button" wire:click="next" class="btn btn-primary">
                @if($currentStep === end($this->steps()))
                    Complete
                @else
                    Next
                @endif
            </button>
        </div>
    </div>
</div>
```

### Registering and Using the Wizard in Your Application

Register your wizard component in your Livewire configuration:

```php
// In your AppServiceProvider or a dedicated LivewireServiceProvider
use Livewire\Livewire;
use App\Livewire\UserRegistrationWizard;

public function boot()
{
    Livewire::component('user-registration-wizard', UserRegistrationWizard::class);
}
```

Then, include the component in your Blade view:

```blade
<livewire:user-registration-wizard />
```

## Blade Directives

The package provides two Blade directives for conditional rendering based on the current step:

1. `@step('step-key')`: Renders content only if the current step matches the specified key.
2. `@end-step`: Closes the conditional block.

Example:

```blade
@step('account')
    <!-- Content for the account step -->
@end-step

@step('profile')
    <!-- Content for the profile step -->
@end-step
```

## Example Implementation

Here's a complete example of a wizard implementation:

```php
<?php

namespace App\Livewire;

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

    public function computeNextStep(string $next): string
    {
        // You can add custom logic here to determine the next step
        return $next;
    }

    public function computePreviousStep(string $previous): string
    {
        // You can add custom logic here to determine the previous step
        return $previous;
    }

    public function render()
    {
        return view('livewire.example-wizard');
    }
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
