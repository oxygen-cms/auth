<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('modules.auth', function($schema) {
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

    $schema->makeFields([
        '' => [
            '' => [
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