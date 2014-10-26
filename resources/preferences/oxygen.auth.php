<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('oxygen.auth', function($schema) {
    $schema->setTitle('Authentication');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/auth::config'));

    $routesByName = function() {
        $options = [];
        foreach(Route::getRoutes()->getRoutes() as $route) {
            $name = $route->getName();
            if($name !== null) {
                $options[$name] = $name;
            }
        }
        return $options;
    };

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
            'Appearance' => [
                [
                    'name' => 'theme',
                    'type' => 'select',
                    'options' => $themes,
                    'validationRules' => [
                        'in:' . implode(',', array_keys($themes))
                    ]
                ],
            ],
            'Routes' => [
                [
                    'name' => 'dashboard',
                    'type' => 'select',
                    'options' => $routesByName,
                    'validationRules' => ['route_exists:name']
                ],
                [
                    'name' => 'home',
                    'type' => 'select',
                    'options' => $routesByName,
                    'validationRules' => ['route_exists:name']
                ]
            ]
        ]
    ]);
});