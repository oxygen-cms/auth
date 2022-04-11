<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;

class CreateAuthPreferences extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->make();
        $item->setKey('appearance.auth');
        $item->setPreferences([
            'theme' => 'autumn',
            'logo' => '/vendor/oxygen/ui-theme/img/icon/apple-touch-icon-180x180.png'
        ]);
        $preferences->persist($item, false);

        $item = $preferences->make();
        $item->setKey('modules.auth');
        $item->setPreferences([]);
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
