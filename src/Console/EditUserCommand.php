<?php

namespace Oxygen\Auth\Console;

use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Console\Command;

use Oxygen\Data\Exception\InvalidEntityException;

class EditUserCommand extends Command {

	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'user:edit {id} {cmd} {value?}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */

	protected $description = 'Modifies fields on a user.';

    /**
     * Execute the console command.
     *
     * @param \Oxygen\Auth\Repository\UserRepositoryInterface $users
     * @param \Oxygen\Auth\Repository\GroupRepositoryInterface $groups
     * @return mixed
     * @throws InvalidEntityException
     */
	public function handle(UserRepositoryInterface $users, GroupRepositoryInterface $groups) {
        $user = $users->find((int) $this->argument('id'));
        $cmd = $this->argument('cmd');

        if($cmd === 'setGroup') {
            $allGroups = $groups->all();
            $groupNames = [];
            $mappedGroups = [];
            foreach($allGroups as $group) {
                $display = $group->getName() . ' - ' . $group->getDescription();
                $groupNames[] = $display;
                $mappedGroups[$display] = $group;
            }

            $group = $mappedGroups[$this->choice('Choose a new group for this user', $groupNames)];
            $user->setGroup($group);

        } else {
            call_user_func_array([$user, $cmd], $this->hasArgument('value') ? [$this->argument('value')] : []);
        }
		$users->persist($user);
        dump($user->toArray());
        $this->info('User modified.');
	}

}
