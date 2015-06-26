<?php

namespace Oxygen\Auth;

use Illuminate\Contracts\Http\Kernel;
use Oxygen\Auth\Middleware\Authenticate;
use Oxygen\Auth\Middleware\Permissions;
use Oxygen\Auth\Middleware\RedirectIfAuthenticated;
use Oxygen\Auth\Permissions\PermissionsInterface;
use Oxygen\Auth\Permissions\SimplePermissionsSystem;
use Oxygen\Auth\Repository\DoctrineGroupRepository;
use Oxygen\Auth\Repository\DoctrineUserRepository;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Data\BaseServiceProvider;
use Oxygen\Preferences\PreferencesManager;
use Oxygen\Preferences\Transformer\JavascriptTransformer;

class AuthServiceProvider extends BaseServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'oxygen/auth');
        $this->loadEntitiesFrom(__DIR__ . '/Entity');

        $this->publishes([
            __DIR__ . '/../resources/lang' => base_path('resources/lang/vendor/oxygen/auth'),
        ]);

		$this->app['router']->middleware('oxygen.auth', Authenticate::class);
		$this->app['router']->middleware('oxygen.guest', RedirectIfAuthenticated::class);
		$this->app['router']->middleware('oxygen.permissions', Permissions::class);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		// Permissions System
        $this->app->bind(PermissionsInterface::class, SimplePermissionsSystem::class);

        // Repositories
        $this->app->bind(UserRepositoryInterface::class, DoctrineUserRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, DoctrineGroupRepository::class);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */

	public function provides() {
		return [
			PermissionsInterface::class,
            UserRepositoryInterface::class,
            GroupRepositoryInterface::class
		];
	}

}
