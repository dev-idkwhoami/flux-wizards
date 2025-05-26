<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wizard Storage
    |--------------------------------------------------------------------------
    |
    | This option controls the default storage mechanism for wizard data.
    | By default, wizard data is stored in the Livewire component's data.
    | You can change this to use session or database storage instead.
    |
    | Supported: "component", "session", "database"
    |
    */
    'storage' => 'component',

    /*
    |--------------------------------------------------------------------------
    | Step Views Directory
    |--------------------------------------------------------------------------
    |
    | This value is the directory where step views are stored.
    | By default, step views are loaded from the 'steps' directory.
    |
    */
    'steps_directory' => 'steps',
];
