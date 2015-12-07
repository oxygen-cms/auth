<?php

namespace Oxygen\Auth\Console;

use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Console\Command;

use Oxygen\Data\Exception\InvalidEntityException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\Table;

class MakeUserCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */

	protected $name = 'make:user';

	/**
	 * The console command description.
	 *
	 * @var string
	 */

	protected $description = 'Creates a new user.';

	/**
	 * Execute the console command.
	 *
	 * @param \Oxygen\Auth\Repository\UserRepositoryInterface  $users
	 * @param \Oxygen\Auth\Repository\GroupRepositoryInterface $groups
	 * @return mixed
	 */
	public function handle(UserRepositoryInterface $users, GroupRepositoryInterface $groups) {
		$username = $this->ask('Username');

		$fullName = $this->anticipate('Full Name', [$username]);
		$email = $this->ask('Email Address');
		$password = $this->secret('Password');

		$allGroups = $groups->all();

		if(empty($allGroups) && $this->confirm('There are no groups in the database. Would you like to create one? [y|N]')) {
			$groupName = $this->ask('Group Name');
			$this->call('make:group', ['name' => $groupName]);
			$allGroups = $groups->all();
		}

		if(empty($allGroups)) {
			$this->error('Cannot create a user without a group');
		}

		$groupNames = [];
		$mappedGroups = [];
		foreach($allGroups as $group) {
			$groupNames[] = $group->getName();
			$mappedGroups[$group->getName()] = $group;
		}

		$group = $mappedGroups[$this->choice('Group', $groupNames)];

		$preferences = file_get_contents(__DIR__ . '/../../resources/seed/preferences.json');
		
		try {
			$item = $users->make();
			$item->setAllFillable(true);
			$item->setUsername($username);
			$item->setFullName($fullName);
			$item->setEmail($email);
			$item->setPreferences($preferences);
			$item->setPassword($password);
			$item->setGroup($group);
			$users->persist($item);

			$this->info('User Created');
		} catch(InvalidEntityException $e) {
			$this->error($e->getErrors()->first());
		}
	}

}
