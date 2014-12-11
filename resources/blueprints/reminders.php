<?php

use Oxygen\Core\Action\Factory\ActionFactory;

Blueprint::make('Reminders', function($blueprint) {
    $blueprint->setController('Oxygen\Auth\Controller\RemindersController');
    $blueprint->setDisplayName('Reminders', Blueprint::SINGULAR);
    $blueprint->setDisplayName('Reminders', Blueprint::PLURAL);

    $factory = new ActionFactory();

    $blueprint->makeAction([
        'name' => 'getRemind',
        'pattern' => 'remind',
        'beforeFilters' => ['oxygen.guest']
    ], $factory);

    $blueprint->makeAction([
        'name' => 'postRemind',
        'pattern' => 'remind',
        'method' => 'POST',
        'beforeFilters' => ['oxygen.guest', 'oxygen.csrf']
    ], $factory);

    $blueprint->makeAction([
        'name' => 'getReset',
        'pattern' => 'reset',
        'beforeFilters' => ['oxygen.guest']
    ], $factory);

    $blueprint->makeAction([
        'name' => 'postReset',
        'pattern' => 'logout',
        'method' => 'POST',
        'beforeFilters' => ['oxygen.guest', 'oxygen.csrf']
    ], $factory);
});