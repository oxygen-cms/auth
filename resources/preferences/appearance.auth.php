<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('appearance.auth', function($schema) {
    $schema->setTitle('Login & Logout');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/auth::config'));

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

    $schema->makeFields([
        '' => [
            '' => [
                [
                    'name' => 'theme',
                    'type' => 'select',
                    'options' => $themes,
                    'validationRules' => [
                        'in:' . implode(',', array_keys($themes))
                    ]
                ]
            ]
        ]
    ]);
});