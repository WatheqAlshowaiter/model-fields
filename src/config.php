<?php

return [
    /**
     * Enable or disable the `getRequiredFields` macro for Eloquent models.
     *
     * When enabled, you can use the `getRequiredFields` macro directly on your models,
     * e.g., `User::getRequiredFields()`.
     *
     * When disabled, the macro will not be registered, and attempting to call it
     * will result in a `BadMethodCallException`.
     */
    'enable_macro' => true,
];
