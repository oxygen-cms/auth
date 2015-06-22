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
use Oxygen\Core\Html\Navigation\Navigation;
use Oxygen\Data\BaseServiceProvider;
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
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'oxygen/auth');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'oxygen/auth');
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/config.php', 'oxygen.auth');

        $this->publishes([
            __DIR__ . '/../resources/config/config.php' => config_path('oxygen/auth.php'),
            __DIR__ . '/../resources/lang' => base_path('resources/lang/vendor/oxygen/auth'),
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/oxygen/auth')
        ]);

        $this->loadEntitiesFrom(__DIR__ . '/Entity');

		$this->app['router']->middleware('oxygen.auth', Authenticate::class);
		$this->app['router']->middleware('oxygen.guest', RedirectIfAuthenticated::class);
		$this->app['router']->middleware('oxygen.permissions', Permissions::class);

        $this->app['oxygen.blueprintManager']->loadDirectory(__DIR__ . '/../resources/blueprints');
        $this->app['oxygen.preferences']->loadDirectory(__DIR__ . '/../resources/preferences');

        $this->addNavigationItems();
        $this->addPreferencesToLayout();
	}

	/**
	 * Adds items the the admin navigation.
	 *
	 * @return void
	 */

	public function addNavigationItems() {
		$blueprints = $this->app['oxygen.blueprintManager'];
		$blueprint = $blueprints->get('Auth');
		$nav = $this->app['oxygen.navigation'];

		$nav->add($blueprint->getToolbarItem('getInfo'));
		$nav->add($blueprint->getToolbarItem('getPreferences'));
		$nav->add($blueprint->getToolbarItem('postLogout'));
	}

	/**
     * Adds some embedded Javascript code that contains the user's preferences.
     *
     * @return void
     */

    protected function addPreferencesToLayout() {
        $this->app['events']->listen('oxygen.layout.body.after', function() {
		    if($this->app['auth']->check()) {
		        $javascriptTransformer = new JavascriptTransformer();
		        echo $javascriptTransformer->fromRepository($this->app['auth']->user()->getPreferences(), 'user');
		    }
        });
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
