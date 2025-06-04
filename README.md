# Flux Wizard

A Laravel package that provides functionality for building tree-based wizard-style multi-step forms.

## Features

- Tree-based system for organizing wizard steps
- Fluent, chainable API for defining wizards and steps
- Each step can hold an array of child steps
- Conditional step flow using closure-based logic
- Persistent state across steps
- Livewire integration with the HasWizard trait

## Installation

You can install the package via composer:

```bash
composer require idkwhoami/flux-wizards
```

The package will automatically register its service provider.

## Usage

This package provides a simple way to create multi-step wizards in your Laravel application using Livewire. Below is a comprehensive example of how to use the package.

### Creating a Wizard

To create a wizard, use the `HasWizard` trait in your Livewire component and implement the `createWizard` method using the fluent API:

```php
<?php

namespace App\Http\Livewire;

use Idkwhoami\FluxWizards\Concretes\Step;
use Idkwhoami\FluxWizards\Concretes\Wizard;
use Idkwhoami\FluxWizards\Traits\HasWizard;
use Livewire\Component;

class UserRegistrationWizard extends Component
{
    use HasWizard;

    /**
     * Create the wizard instance.
     *
     * @return Wizard
     */
    protected function createWizard()
    {
        // Create a new wizard with initial data using the fluent API
        return Wizard::make($this->data)
            ->name('user-registration')
            ->steps([
                Step::make('account')
                    ->name('Account Information')
                    ->rules([
                        'email' => 'required|email',
                        'password' => 'required|min:8',
                        'password_confirmation' => 'required|same:password',
                    ])
                    ->fields([
                        'email',
                        'password',
                        'password_confirmation',
                    ]),

                Step::make('profile')
                    ->name('Profile Information')
                    ->rules([
                        'name' => 'required|string|max:255',
                        'bio' => 'nullable|string|max:1000',
                    ])
                    ->fields([
                        'name',
                        'bio',
                        'setup_preferences', // Optional field to control flow
                    ]),

                Step::make('preferences')
                    ->name('User Preferences')
                    ->rules([
                        'notifications' => 'boolean',
                        'theme' => 'required|in:light,dark,system',
                    ])
                    ->fields([
                        'notifications',
                        'theme',
                    ]),
            ])
            ->flow(function ($currentStep, $data) {
                // Example of conditional flow logic
                if ($currentStep === 'profile' && isset($data['profile']['setup_preferences']) && $data['profile']['setup_preferences']) {
                    return 'preferences';
                }

                // Default to the next step in sequence
                return null;
            });
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return $this->renderWizard();
    }

    /**
     * Handle the form submission when the wizard is completed.
     *
     * @return void
     */
    public function complete()
    {
        // Validate the final step
        $this->nextStep();

        // If we're still on the last step, there were validation errors
        if (!$this->wizard->isLastStep()) {
            return;
        }

        // Process the completed wizard data
        // This is where you would typically create a user, etc.
        $userData = [
            'email' => $this->data['account']['email'],
            'password' => bcrypt($this->data['account']['password']),
            'name' => $this->data['profile']['name'],
        ];

        // Create the user
        // $user = \App\Models\User::create($userData);

        // Set preferences if they were provided
        if (isset($this->data['preferences'])) {
            // $user->preferences()->create([
            //     'notifications' => $this->data['preferences']['notifications'] ?? false,
            //     'theme' => $this->data['preferences']['theme'] ?? 'system',
            // ]);
        }

        // Redirect or show success message
        session()->flash('message', 'Registration completed successfully!');

        // Reset the wizard
        $this->wizard = $this->createWizard();
        $this->data = $this->wizard->getData();
    }
```

### Creating Step Views

Create a Blade view for each step in your wizard. The views should be placed in the `resources/views/steps` directory. The step views are included in the wizard component using the `@include` directive in the `simple-wizard.blade.php` view:

```blade
@include("flux-wizards::steps.{$currentStep->getKey()}")
```

Below are examples of step views that correspond to the steps defined in the wizard examples above:

#### Account Step

```blade
<!-- resources/views/steps/account.blade.php -->
<div class="step-content">
    <div class="form-group">
        <label for="account.email">Email</label>
        <input type="email" id="account.email" wire:model="data.account.email" class="form-control">
        @error('account.email') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="account.password">Password</label>
        <input type="password" id="account.password" wire:model="data.account.password" class="form-control">
        @error('account.password') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="account.password_confirmation">Confirm Password</label>
        <input type="password" id="account.password_confirmation" wire:model="data.account.password_confirmation" class="form-control">
        @error('account.password_confirmation') <span class="error">{{ $message }}</span> @enderror
    </div>
</div>
```

#### Profile Step

```blade
<!-- resources/views/steps/profile.blade.php -->
<div class="step-content">
    <div class="form-group">
        <label for="profile.name">Name</label>
        <input type="text" id="profile.name" wire:model="data.profile.name" class="form-control">
        @error('profile.name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="profile.bio">Bio</label>
        <textarea id="profile.bio" wire:model="data.profile.bio" class="form-control" rows="4"></textarea>
        @error('profile.bio') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <div class="form-check">
            <input type="checkbox" id="profile.setup_preferences" wire:model="data.profile.setup_preferences" class="form-check-input">
            <label for="profile.setup_preferences" class="form-check-label">Set up preferences now</label>
        </div>
    </div>
</div>
```

#### Preferences Step

```blade
<!-- resources/views/steps/preferences.blade.php -->
<div class="step-content">
    <div class="form-group">
        <div class="form-check">
            <input type="checkbox" id="preferences.notifications" wire:model="data.preferences.notifications" class="form-check-input">
            <label for="preferences.notifications" class="form-check-label">Enable notifications</label>
            @error('preferences.notifications') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group">
        <label for="preferences.theme">Theme</label>
        <select id="preferences.theme" wire:model="data.preferences.theme" class="form-control">
            <option value="">Select a theme</option>
            <option value="light">Light</option>
            <option value="dark">Dark</option>
            <option value="system">System</option>
        </select>
        @error('preferences.theme') <span class="error">{{ $message }}</span> @enderror
    </div>
</div>
```

### Registering and Using the Wizard in Your Application

#### 1. Register Your Livewire Component

First, register your wizard component in your Livewire configuration:

```php
// In your AppServiceProvider or a dedicated LivewireServiceProvider
use Livewire\Livewire;
use App\Http\Livewire\UserRegistrationWizard;

public function boot()
{
    Livewire::component('user-registration-wizard', UserRegistrationWizard::class);
}
```

#### 2. Create a Blade View for Your Page

Create a Blade view that will contain your wizard:

```blade
<!-- resources/views/auth/register-wizard.blade.php -->
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-6">User Registration Wizard</h1>

                    <!-- Include the Livewire component -->
                    <livewire:user-registration-wizard />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

#### 3. Add a Route to Your Page

Add a route to your page in your `routes/web.php` file:

```php
Route::get('/register-wizard', function () {
    return view('auth.register-wizard');
})->name('register.wizard');
```

Now users can access your wizard at `/register-wizard`.

### Conditional Step Flow

You can control the flow of your wizard using the `flow` method. This allows you to define custom logic for determining which step comes next:

```php
$wizard->flow(function ($currentStep, $data) {
    // Custom flow logic based on current step and data
    if ($currentStep === 'profile') {
        if (isset($data['profile']['account_type']) && $data['profile']['account_type'] === 'business') {
            return 'business-details';
        } else {
            return 'personal-details';
        }
    }

    // Return null to use default step sequence
    return null;
});
```

### Handling Form Submission

You can add a `complete` method to your wizard component to handle form submission when the wizard is completed:

```php
public function complete()
{
    // Validate the final step
    $this->nextStep();

    // If we're still on the last step, there were validation errors
    if (!$this->wizard->isLastStep()) {
        return;
    }

    // Process the completed wizard data
    // This is where you would typically create a user, etc.

    // Redirect or show success message
    session()->flash('message', 'Registration completed successfully!');
}
```

## Customizing the Wizard View

The package includes a default Blade view for the wizard component (`simple-wizard.blade.php`). You can publish this view to customize it:

```bash
php artisan vendor:publish --tag="flux-wizards-views"
```

This will copy the view files to `resources/views/vendor/flux-wizards/` in your application, where you can modify them.

### Default Wizard View

The default wizard view (`simple-wizard.blade.php`) includes:

```blade
<div>
    <div class="wizard-step">
        @if($currentStep)
            <h2>{{ $currentStep->getName() }}</h2>

            {{-- Include the current step's view --}}
            @include("flux-wizards::steps.{$currentStep->getKey()}")

            <div class="wizard-navigation">
                @if(!$isFirstStep)
                    <button type="button" wire:click="previousStep" class="wizard-back-button">
                        Back
                    </button>
                @endif

                <button type="button" wire:click="nextStep" class="wizard-next-button">
                    @if($isLastStep)
                        Finish
                    @else
                        Next
                    @endif
                </button>
            </div>
        @else
            <p>No steps defined for this wizard.</p>
        @endif
    </div>
</div>
```

### Creating a Custom Wizard View

You can create a completely custom view for your wizard. For example, you might want to add a progress bar or change the styling:

```blade
<!-- resources/views/vendor/flux-wizards/simple-wizard.blade.php -->
<div class="wizard-container">
    @if($currentStep)
        <!-- Progress bar -->
        <div class="wizard-progress">
            @php
                $totalSteps = count($this->wizard->getSteps());
                $currentStepIndex = array_search($currentStep->getKey(), array_keys($this->wizard->getSteps())) + 1;
                $progressPercentage = ($currentStepIndex / $totalSteps) * 100;
            @endphp
            <div class="progress-bar">
                <div class="progress" style="width: {{ $progressPercentage }}%"></div>
            </div>
            <div class="step-counter">Step {{ $currentStepIndex }} of {{ $totalSteps }}</div>
        </div>

        <!-- Step title -->
        <h2 class="step-title">{{ $currentStep->getName() }}</h2>

        <!-- Step content -->
        <div class="step-content">
            @include("flux-wizards::steps.{$currentStep->getKey()}")
        </div>

        <!-- Navigation buttons -->
        <div class="wizard-navigation">
            @if(!$isFirstStep)
                <button type="button" wire:click="previousStep" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
            @endif

            <button type="button" wire:click="nextStep" class="btn btn-primary">
                @if($isLastStep)
                    Complete <i class="fas fa-check"></i>
                @else
                    Next <i class="fas fa-arrow-right"></i>
                @endif
            </button>
        </div>
    @else
        <div class="alert alert-warning">
            <p>No steps defined for this wizard.</p>
        </div>
    @endif
</div>
```

Don't forget to add the appropriate CSS styles to make your custom wizard view look good!

## Configuration

You can publish the configuration file with:

```bash
php artisan vendor:publish --tag="flux-wizards-config"
```

This will publish the configuration file to `config/flux-wizards.php`.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
