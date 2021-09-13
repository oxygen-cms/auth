<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;
use Oxygen\Preferences\Repository;

class CreateAuthPreferences extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->make();
        $item->setKey('appearance.auth');
        $data = new Repository([]);
        $data->set('theme', 'autumn');
        $data->set('logo', '/vendor/oxygen/ui-theme/img/icon/apple-touch-icon-180x180.png');
        $item->setPreferences($data);
        $preferences->persist($item, false);

        $item = $preferences->make();
        $item->setKey('modules.auth');
        $data = new Repository([]);
        $item->setPreferences($data);
        $preferences->persist($item, false);

        $preferences->flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $preferences->delete($preferences->findByKey('appearance.auth'), false);
        $preferences->delete($preferences->findByKey('modules.auth'), false);
        $preferences->flush();
    }
}
