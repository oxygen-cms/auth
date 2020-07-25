<?php

namespace Oxygen\Auth;

use Illuminate\Routing\Router;
use Oxygen\Auth\Console\MakeGroupCommand;
use Oxygen\Auth\Console\MakeUserCommand;
use Oxygen\Auth\Middleware\Authenticate;
use Oxygen\Auth\Middleware\ConfirmTwoFactorCode;
use Oxygen\Auth\Middleware\Permissions;
use Oxygen\Auth\Middleware\RedirectIfAuthenticated;
use Oxygen\Auth\Middleware\RequireTwoFactorDisabled;
use Oxygen\Auth\Middleware\RequireTwoFactorEnabled;
use Oxygen\Auth\Permissions\PermissionsInterface;
use Oxygen\Auth\Permissions\SimplePermissionsSystem;
use Oxygen\Auth\Repository\DoctrineGroupRepository;
use Oxygen\Auth\Repository\DoctrineUserRepository;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\BaseServiceProvider;

class AuthServiceProvider extends BaseServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot(Router $router) {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'oxygen/auth');

        $this->publishes([
            __DIR__ . '/../resources/lang' => base_path('resources/lang/vendor/oxygen/auth'),
        ]);

		$router->aliasMiddleware('oxygen.auth', Authenticate::class);
        $router->aliasMiddleware('oxygen.guest', RedirectIfAuthenticated::class);
        $router->aliasMiddleware('oxygen.permissions', Permissions::class);
        $router->aliasMiddleware('2fa.require', RequireTwoFactorEnabled::class);
        $router->aliasMiddleware('2fa.confirm', ConfirmTwoFactorCode::class);
        $router->aliasMiddleware('2fa.disabled', RequireTwoFactorDisabled::class);

		$this->commands(MakeUserCommand::class);
		$this->commands(MakeGroupCommand::class);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		$this->loadEntitiesFrom(__DIR__ . '/Entity');

		// Permissions System
        $this->app->bind(PermissionsInterface::class, SimplePermissionsSystem::class);

        // Repositories
        $this->app->bind(UserRepositoryInterface::class, DoctrineUserRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, DoctrineGroupRepository::class);
	}

}
