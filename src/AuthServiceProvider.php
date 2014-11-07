<?php

namespace Oxygen\Auth;

use Oxygen\Core\Html\Navigation\Navigation;
use Oxygen\Core\Support\ServiceProvider;
use Oxygen\Preferences\Transformer\JavascriptTransformer;

class AuthServiceProvider extends ServiceProvider {

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
		$this->package('oxygen/auth', 'oxygen/auth', __DIR__ . '/../resources');
        $this->entities(__DIR__ . '/Entity');

		$this->app['router']->filter('oxygen.auth', 'Oxygen\Auth\Filter\AuthFilter@auth');
		$this->app['router']->filter('oxygen.guest', 'Oxygen\Auth\Filter\AuthFilter@guest');
		$this->app['router']->filter('oxygen.permissions', 'Oxygen\Auth\Filter\PermissionsFilter');

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

		$nav->add($blueprint->getToolbarItem('getInfo'), Navigation::SECONDARY);
		$nav->add($blueprint->getToolbarItem('getPreferences'), Navigation::SECONDARY);
		$nav->add($blueprint->getToolbarItem('postLogout'), Navigation::SECONDARY);

		if($this->app['auth']->check()) {
			$name = $this->app['auth']->user()->getFullName();
			$this->app['oxygen.navigation']->order(Navigation::SECONDARY, [
				'System' => ['marketplace.getHome', 'preferences.getView'],
				$name => ['auth.getInfo', 'auth.getPreferences', 'auth.postLogout']
			]);
		}
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
        $this->app->bind('Oxygen\Auth\Permissions\PermissionsInterface', 'Oxygen\Auth\Permissions\SimplePermissionsSystem');

        // Repositories
        $this->app->bind('Oxygen\Auth\Repository\UserRepositoryInterface', 'Oxygen\Auth\Repository\DoctrineUserRepository');
        $this->app->bind('Oxygen\Auth\Repository\GroupRepositoryInterface', 'Oxygen\Auth\Repository\DoctrineGroupRepository');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */

	public function provides() {
		return [
			'Oxygen\Auth\Permissions\PermissionsInterface'
		];
	}

}
