<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\PreferencesManager;

class CreateAuthModules extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = app(PreferencesManager::class);

        $schema = $preferences->getSchema('modules.auth');
        $schema->getRepository()->set('dashboard', 'dashboard.main');
        $schema->storeRepository();
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        $preferences = app(PreferencesManager::class);

        $schema = $preferences->getSchema('modules.auth');
        $schema->getRepository()->set('dashboard', null);
        $schema->storeRepository();
    }
}
