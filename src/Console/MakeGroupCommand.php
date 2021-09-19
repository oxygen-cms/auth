<?php

namespace Oxygen\Auth\Console;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Core\Console\Command;

use Oxygen\Data\Exception\InvalidEntityException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\Table;

class MakeGroupCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */

    protected $signature = 'group:add {nickname : an easy-to-type name for the group}';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Creates a new Group entity.';

    const ALL_PERMISSIONS = 'all permissions (superadmin)';
    const NO_PERMISSIONS = 'no permissions';

    /**
     * Execute the console command.
     *
     * @param GroupRepositoryInterface $groups
     * @return mixed
     */
    public function handle(GroupRepositoryInterface $groups, Permissions $permissions) {
        $nickname = $this->argument('nickname');
        $name = $this->askWithCompletion('Enter a display name for the group', [Str::ucfirst($nickname)]);
        $icon = $this->askWithCompletion('Enter an icon for this group', ['user', 'user-slash', 'user-tag', 'user-shield', 'user-secret', 'users-cog', 'user-nurse', 'user-ninja', 'user-lock']);
        $description = $this->ask('Enter a description for this group');
        $permissionsMode = $this->choice('Assign permissions to this group', [self::ALL_PERMISSIONS, self::NO_PERMISSIONS], self::NO_PERMISSIONS);

        $permissionsData = $this->makePermissions($permissionsMode, $permissions);

        $this->info('Using default preferences');
        $defaultPreferences = json_decode(file_get_contents(__DIR__ . '/../../resources/seed/group_preferences.json'), true);

        try {
            $item = $groups->make();
            $item->setName($name);
            $item->setIcon($icon);
            $item->setNickname($nickname);
            $item->setDescription($description);
            $item->setPreferences($defaultPreferences);
            $item->setPermissions($permissionsData);
            $groups->persist($item);

            $this->info('Group ' . $item->getNickname() . ' created with id ' . $item->getId());
            return 0;
        } catch(InvalidEntityException $e) {
            $this->error($e->getErrors()->first());
            return 1;
        }
    }

    private function makePermissions(string $mode, Permissions $permissions): array {
        if($mode === self::ALL_PERMISSIONS) {
            $perms = [];
            foreach($permissions->getAllPermissions() as $perm) {
                Arr::set($perms, $perm, true);
            }
            return $perms;
        } else {
            return [];
        }
    }

}
