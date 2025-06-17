# Flux Wizards

A Laravel package that provides functionality for building wizard-style multi-step forms with Livewire Flux.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Usage](#usage)
  - [Quick Start](#quick-start)
  - [Creating a Wizard](#creating-a-wizard)
  - [Required Methods](#required-methods)
  - [Navigation Methods](#navigation-methods)
  - [Creating Step Views](#creating-step-views)
  - [Blade Directives](#blade-directives)
  - [Customizing the Wizard View](#customizing-the-wizard-view)
  - [Localization](#localization)
- [API Reference](#api-reference)
  - [Wizard Class](#wizard-class)
  - [Step Class](#step-class)
- [License](#license)

## Features

- Object-oriented API for defining wizard steps and their relationships
- Navigation between steps with next() and previous() methods
- Custom flow logic for determining step progression
- Validation rules for each step
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
- Livewire Flux Pro 2.1+

## Usage

### Quick Start

To quickly implement a wizard in your Livewire component:

1. Add the `HasWizard` trait to your Livewire component
2. Implement the required `createWizard()` and `complete()` methods
3. Create views for each step in your wizard
4. Use the navigation methods `nextStep()` and `previousStep()` for moving between steps

```php
use Idkwhoami\FluxWizards\Concretes\Step;
use Idkwhoami\FluxWizards\Concretes\Wizard;
use Idkwhoami\FluxWizards\Traits\HasWizard;
use Livewire\Component;

class MyWizard extends Component
{
    use HasWizard;

    public array $data = [];

    protected function createWizard(): Wizard
    {
        $step1 = Step::make('step1')->label('First Step');
        $step2 = Step::make('step2')->label('Second Step');

        $step1->children([$step2]);

        return Wizard::make('my-wizard')
            ->root($step1)
            ->directory('wizards.my-wizard');
    }

    protected function complete(array $data): void
    {
        // Process the collected data
        session()->flash('message', 'Wizard completed!');
    }
}
```

### Creating a Wizard

To create a wizard, use the `HasWizard` trait in your Livewire component and implement the required methods:

```php
<?php

namespace App\Livewire;

use Idkwhoami\FluxWizards\Concretes\Step;
use Idkwhoami\FluxWizards\Concretes\Wizard;
use Idkwhoami\FluxWizards\Traits\HasWizard;
use Livewire\Component;

class UserRegistrationWizard extends Component
{
    use HasWizard;

    // Data that will be collected through the wizard
    public array $data = [
        'account' => [],
        'profile' => [],
        'billing' => [],
    ];

    /**
     * Create the wizard instance.
     *
     * @return Wizard
     */
    protected function createWizard(): Wizard
    {
        // Create the root step
        $accountStep = Step::make('account')
            ->label('Account Information')
            ->rules([
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

        // Create child steps
        $profileStep = Step::make('profile')
            ->label('Profile Information')
            ->rules([
                'name' => 'required',
                'bio' => 'nullable',
            ]);

        $billingStep = Step::make('billing')
            ->label('Billing Information')
            ->rules([
                'card_number' => 'required|numeric',
                'expiry' => 'required',
            ]);

        // Define custom flow logic (optional)
        $accountStep->flow(function(Step $current, array $data, Step $next) {
            // Skip billing if user is on a free plan
            if ($next->is('billing') && ($data['account.plan'] ?? '') === 'free') {
                return false;
            }

            return true;
        });

        // Set up the step hierarchy
        $accountStep->children([
            $profileStep->children([
                $billingStep
            ])
        ]);

        // Create and return the wizard
        return Wizard::make('user-registration')
            ->root($accountStep)
            ->directory('wizards.user-registration'); // Directory where step views are stored
    }

    /**
     * Handle completion of the wizard.
     *
     * @param array $data
     * @return void
     */
    protected function complete(array $data): void
    {
        // Process the collected data
        // For example, create a user, set up billing, etc.

        // Redirect or show a success message
        session()->flash('message', 'Registration completed successfully!');
        $this->redirect('/dashboard');
    }
}
```

### Required Methods

When using the `HasWizard` trait, you must implement the following methods:

1. `createWizard()`: Returns a configured Wizard instance with steps.
2. `complete(array $data)`: Handles the final submission when all steps are completed.

### Navigation Methods

The `HasWizard` trait provides two methods for navigating between steps:

1. `nextStep()`: Move to the next step in the sequence or complete the wizard if on the last step.
2. `previousStep()`: Move to the previous step in the sequence.

### Creating Step Views

Create Blade views for each step in your wizard. The views should be placed in the directory specified in the `directory()` method of your Wizard:

```blade
<!-- resources/views/wizards/user-registration/account.blade.php -->
<div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" wire:model="data.account.email">
        @error('data.account.email') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" wire:model="data.account.password">
        @error('data.account.password') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="plan">Plan</label>
        <select id="plan" wire:model="data.account.plan">
            <option value="free">Free</option>
            <option value="premium">Premium</option>
        </select>
    </div>
</div>
```

```blade
<!-- resources/views/wizards/user-registration/profile.blade.php -->
<div>
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" wire:model="data.profile.name">
        @error('data.profile.name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" wire:model="data.profile.bio"></textarea>
        @error('data.profile.bio') <span class="error">{{ $message }}</span> @enderror
    </div>
</div>
```

```blade
<!-- resources/views/wizards/user-registration/billing.blade.php -->
<div>
    <div class="form-group">
        <label for="card_number">Card Number</label>
        <input type="text" id="card_number" wire:model="data.billing.card_number">
        @error('data.billing.card_number') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="expiry">Expiry Date</label>
        <input type="text" id="expiry" wire:model="data.billing.expiry">
        @error('data.billing.expiry') <span class="error">{{ $message }}</span> @enderror
    </div>
</div>
```

### Blade Directives

The package provides two Blade directives for conditional rendering based on the current step:

1. `@step('step-key')`: Renders content only if the current step matches the specified key.
2. `@endstep`: Closes the conditional block.

Example:

```blade
@step('account')
    <!-- Content for the account step -->
@endstep

@step('profile')
    <!-- Content for the profile step -->
@endstep
```

### Customizing the Wizard View

The package comes with a default wizard view that includes navigation buttons and error handling. You can publish this view to customize it:

```bash
php artisan vendor:publish --provider="Idkwhoami\FluxWizards\FluxWizardsServiceProvider"
```

### Localization

The package includes translations for navigation buttons. You can publish the language files to customize them:

```bash
php artisan vendor:publish --tag=flux-wizards-lang
```

## API Reference

### Wizard Class

The `Wizard` class is the main container for your wizard:

- `make(string $name)`: Create a new wizard instance
- `root(Step $root)`: Set the root step of the wizard
- `directory(string $directory)`: Set the directory where step views are stored
- `next()`: Move to the next step
- `previous()`: Move to the previous step
- `getCurrent()`: Get the current step
- `setData(array $data)`: Set data for the wizard
- `getData()`: Get all data from the wizard

### Step Class

The `Step` class represents a single step in your wizard:

- `make(string $name)`: Create a new step instance
- `label(string $label)`: Set a human-readable label for the step
- `view(string $view)`: Set a custom view name (defaults to the step name)
- `rules(array $rules)`: Set validation rules for the step
- `children(array $children)`: Set child steps
- `flow(Closure $flow)`: Set custom flow logic
- `is(string $name)`: Check if the step has a specific name
- `isFirst()`: Check if the step is the first in the sequence
- `isLast()`: Check if the step is the last in the sequence
- `validate(array $data)`: Validate the step data
- `resolveNext(array $data)`: Resolve the next step based on flow logic

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
