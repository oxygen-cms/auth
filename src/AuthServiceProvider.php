<?php

namespace Oxygen\Auth;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Validated;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Oxygen\Auth\Console\EditUserCommand;
use Oxygen\Auth\Console\PermissionsCommand;
use Oxygen\Auth\Console\ListGroupCommand;
use Oxygen\Auth\Console\ListSessionCommand;
use Oxygen\Auth\Console\MakeGroupCommand;
use Oxygen\Auth\Console\MakeUserCommand;
use Oxygen\Auth\Console\UsersListCommand;
use Oxygen\Auth\Listeners\LogAuthentications;
use Oxygen\Auth\Middleware\ConfirmTwoFactorCode;
use Oxygen\Auth\Middleware\EnsureEmailIsVerified;
use Oxygen\Auth\Middleware\RedirectIfAuthenticated;
use Oxygen\Auth\Middleware\RequireTwoFactorDisabled;
use Oxygen\Auth\Middleware\RequireTwoFactorEnabled;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Permissions\PermissionsImplementation;
use Oxygen\Auth\Permissions\TreePermissionsSystem;
use Oxygen\Auth\Repository\AuthenticationLogEntryRepositoryInterface;
use Oxygen\Auth\Repository\DoctrineAuthenticationLogEntryRepository;
use Oxygen\Auth\Repository\DoctrineGroupRepository;
use Oxygen\Auth\Repository\DoctrineUserRepository;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Permissions\PermissionsInterface;
use Oxygen\Data\BaseServiceProvider;
use Oxygen\Core\Preferences\PreferencesManager;
use Oxygen\Core\Preferences\SchemaRegistered;
use Oxygen\Auth\Listeners\EnforceTwoFactorAuth;

class AuthServiceProvider extends BaseServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot(Router $router, Dispatcher $dispatcher) {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'oxygen.auth');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'oxygen/auth');

        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/oxygen/auth'),
        ]);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'oxygen/auth');

        $this->loadRoutesFrom(__DIR__ . '/../resources/routes.php');

        $router->aliasMiddleware('verified', EnsureEmailIsVerified::class);
        $router->aliasMiddleware('oxygen.guest', RedirectIfAuthenticated::class);
        $router->aliasMiddleware('oxygen.permissions', Middleware\Permissions::class);
        $router->aliasMiddleware('oxygen.ownerPermissions', Middleware\OwnerPermissions::class);
        $router->aliasMiddleware('2fa.require', RequireTwoFactorEnabled::class);
        $router->aliasMiddleware('2fa.confirm', ConfirmTwoFactorCode::class);
        $router->aliasMiddleware('2fa.disabled', RequireTwoFactorDisabled::class);

		$this->commands(MakeUserCommand::class);
		$this->commands(MakeGroupCommand::class);
        $this->commands(UsersListCommand::class);
        $this->commands(ListGroupCommand::class);
        $this->commands(ListSessionCommand::class);
        $this->commands(PermissionsCommand::class);
        $this->commands(EditUserCommand::class);

        // each Preferences schema has a corresponding permission which controls access to it
        $dispatcher->listen(SchemaRegistered::class, function(SchemaRegistered $event) {
            $this->app[Permissions::class]->registerPermission('preferences.' . str_replace('.', '_', $event->getKey()));
        });

        $this->app[PreferencesManager::class]->loadDirectory(__DIR__ . '/../resources/preferences');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $dispatcher->listen(Login::class, LogAuthentications::class);
        $dispatcher->listen(Logout::class, LogAuthentications::class);
        $dispatcher->listen(Failed::class, LogAuthentications::class);

        $dispatcher->listen(Validated::class, EnforceTwoFactorAuth::class);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		$this->loadEntitiesFrom(__DIR__ . '/Entity');

		// Permissions System
        $this->app->bind(PermissionsImplementation::class, TreePermissionsSystem::class);
        $this->app->singleton(Permissions::class);

        // Inject our permissions system into the core.
        $this->app->bind(PermissionsInterface::class, Permissions::class);

        // Repositories
        $this->app->bind(UserRepositoryInterface::class, DoctrineUserRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, DoctrineGroupRepository::class);
        $this->app->bind(AuthenticationLogEntryRepositoryInterface::class, DoctrineAuthenticationLogEntryRepository::class);
	}

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
	public function provides() {
	    return [
            PermissionsImplementation::class,
            UserRepositoryInterface::class,
            GroupRepositoryInterface::class,
            AuthenticationLogEntryRepositoryInterface::class
        ];
    }

}
