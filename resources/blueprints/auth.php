<?php

use Carbon\Carbon;
use Oxygen\Core\Action\Factory\ActionFactory;
use Oxygen\Core\Action\Group;
use Oxygen\Core\Form\Type\CustomType;
use Oxygen\Core\Html\Dialog\Dialog;

Blueprint::make('Auth', function($blueprint) {
    $blueprint->setController('Oxygen\Auth\Controller\AuthController');
    $blueprint->setDisplayName('Profile', Blueprint::SINGULAR);
    $blueprint->setDisplayName('Auth', Blueprint::PLURAL);
    $blueprint->setIcon('lock');

    $blueprint->setToolbarOrders([
        'section' => ['getUpdate', 'getChangePassword', 'getPreferences']
    ]);

    $factory = new ActionFactory();

    $blueprint->makeAction([
        'name' => 'getCheck',
        'pattern' => '/',
        'group' => new Group('auth', $this->config->get('oxygen/core::baseURI'))
    ], $factory);

    $blueprint->makeAction([
        'name' => 'getLogin',
        'pattern' => 'login',
        'beforeFilters' => ['oxygen.guest']
    ], $factory);

    $blueprint->makeAction([
        'name' => 'postLogin',
        'pattern' => 'login',
        'method'  => 'POST',
        'beforeFilters' => ['oxygen.guest', 'oxygen.csrf']
    ], $factory);

    $blueprint->makeAction([
        'name' => 'postLogout',
        'pattern' => 'logout',
        'method' => 'POST'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'postLogout',
        'label' => 'Logout',
        'icon'  => 'power-off'
    ]);

    $blueprint->makeAction([
        'name' => 'getLogoutSuccess',
        'pattern' => 'logout',
        'beforeFilters' => ['oxygen.guest']
    ], $factory);

    $blueprint->makeAction([
        'name' => 'getInfo',
        'pattern' => 'profile'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getInfo',
        'label' => 'Profile',
        'icon'  => 'search'
    ]);

    $blueprint->makeAction([
        'name' => 'getUpdate',
        'pattern' => 'update'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getUpdate',
        'label' => 'Edit Profile',
        'icon'  => 'pencil'
    ]);

    $blueprint->makeAction([
        'name' => 'putUpdate',
        'pattern' => 'profile',
        'method' => 'PUT'
    ]);

    $blueprint->makeAction([
        'name' => 'getPreferences',
        'pattern' => 'preferences'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getPreferences',
        'label' => 'Preferences',
        'icon'  => 'cog'
    ]);

    $blueprint->makeAction([
        'name' => 'getChangePassword',
        'pattern' => 'changePassword'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getChangePassword',
        'label' => 'Change Password',
        'icon'  => 'lock'
    ]);

    $blueprint->makeAction([
        'name' => 'postChangePassword',
        'pattern' => 'changePassword',
        'method' => 'POST'
    ]);

    $blueprint->makeAction([
        'name' => 'deleteForce',
        'pattern' => 'terminateAccount',
        'method' => 'DELETE'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'deleteForce',
        'label' => 'Terminate Account',
        'color' => 'red',
        'icon' => 'trash-o',
        'dialog' => new Dialog(Lang::get('oxygen/auth::dialogs.terminateAccount'))
    ]);

    $blueprint->makeFields([
        [
            'name'      => 'username',
            'label'     => 'Username',
            'editable'  => true
        ],
        [
            'name'      => 'fullName',
            'label'     => 'Full Name',
            'type'      => 'text',
            'editable'  => true
        ],
        [
            'name'      => 'email',
            'label'     => 'Email Address',
            'type'      => 'email',
            'editable'  => true
        ],
        [
            'name'      => 'group',
            'label'     => 'Group',
            'typeInstance' => new CustomType(
                function($metadata, $value) {
                    return $value;
                },
                function($metadata, $value) {
                    return $value->getName();
                }
            )
        ],
        [
            'name'      => 'createdAt',
            'label'     => 'Joined',
            'type'      => 'datetime',
            'typeInstance' => new CustomType(
                function($metadata, $value) {
                    return $value;
                },
                function($metadata, $value) {
                    return Carbon::instance($value)->diffForHumans();
                }
            )
        ]
    ]);
});