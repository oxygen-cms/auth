<?php

    use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;
    use Oxygen\Preferences\Loader\DatabaseLoader;

Preferences::register('modules.auth', function($schema) {
    $schema->setTitle('Authentication');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'modules.auth'));

    $routesByName = function() {
        $options = [];
        foreach(app(\Illuminate\Routing\Router::class)->getRoutes() as $route) {
            $name = $route->getName();
            if($name !== null) {
                $options[$name] = $name;
            }
        }
        return $options;
    };

    $schema->makeFields([
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
        ],
        [
            'name' => 'notifyWhenNewDevice',
            'type' => 'toggle',
            'label' => 'Notify user when logging in from a new device'
        ],
        [
            'name' => 'loginLogExpiry',
            'type' => 'number',
            'label' => 'Number of days to keep authentication log entries'
        ]
    ]);
});
