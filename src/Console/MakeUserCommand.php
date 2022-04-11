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

	protected $name = 'user:add';

	/**
	 * The console command description.
	 *
	 * @var string
	 */

	protected $description = 'Adds a new user to the system.';

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
			$groupName = $this->ask('Group Nickname');
			$this->call('group:add', ['nickname' => $groupName]);
			$allGroups = $groups->all();
		}

		if(empty($allGroups)) {
			$this->error('Cannot create a user without a group');
		}

		$groupNames = [];
		$mappedGroups = [];
		foreach($allGroups as $group) {
            $display = $group->getName() . ' - ' . $group->getDescription();
			$groupNames[] = $display;
			$mappedGroups[$display] = $group;
		}

		$group = $mappedGroups[$this->choice('Group', $groupNames)];

		try {
			$item = $users->make();
			$item->setUsername($username);
			$item->setFullName($fullName);
			$item->setEmail($email);
			$item->setPreferences([]);
			$item->setPassword($password);
			$item->setGroup($group);
			$users->persist($item);

			$this->info('User Created');
		} catch(InvalidEntityException $e) {
			$this->error($e->getErrors()->first());
		}
	}

}
