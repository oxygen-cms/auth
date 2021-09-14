<?php

namespace Oxygen\Auth\Session;

use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;

class DoctrineSessionServiceProvider extends ServiceProvider {

    public function boot() {
        $this->app['session']->extend('doctrine', function() {
            return $this->app[DoctrineSessionHandler::class];
        });
    }

    public function register() {
        $this->app->singleton(DoctrineSessionHandler::class, function() {
            return new DoctrineSessionHandler($this->app[EntityManager::class], config('session.lifetime'), $this->app);
        });
    }

    public function provides() {
        return [
            DoctrineSessionHandler::class
        ];
    }

}