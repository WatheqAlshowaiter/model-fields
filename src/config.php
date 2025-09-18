<?php

return [
    /**
     * Enable or disable the `requiredFields` or similar macros for Eloquent models.
     *
     * When enabled, you can use the `requiredFields` macro directly on your models,
     * e.g., `User::requiredFields()`.
     *
     * When disabled, the macro will not be registered, and attempting to call it
     * will result in a `BadMethodCallException`.
     */
    'enable_macro' => true,
];
