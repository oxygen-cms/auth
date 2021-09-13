<?php

use Illuminate\Validation\Rule;
use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;

Preferences::register('appearance.auth', function($schema) {
    $schema->setTitle('Login & Logout');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'appearance.auth'));

    $themes = [
        'autumn' => 'Autumn Leaves',
        'city' => 'City Street',
        'clouds' => 'Clouds',
        'coast' => 'Coast',
        'speckles' => 'Speckles',
        'trees' => 'Trees',
        'waves' => 'Waves',
        'yosemite' => 'Yosemite'
    ];

    $schema->makeField([
        'name' => 'theme',
        'type' => 'select',
        'options' => $themes,
        'validationRules' => [
            Rule::in(array_keys($themes))
        ]
    ]);
});
