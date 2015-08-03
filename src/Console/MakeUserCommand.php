<?php

namespace Oxygen\Auth\Console;

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
	 * @param \Oxygen\Auth\Repository\UserRepositoryInterface $users
	 * @return mixed
	 */
	public function handle(UserRepositoryInterface $users) {
		$username = $this->argument('name');

		$fullName = $this->anticipate('Full Name:', [$username]);
		$email = $this->ask('Email Address:');
		$password = $this->secret('Password: ');

		$preferences = file_get_contents(__DIR__ . '/../../resources/seed/preferences.json');

		try {
			$item = $users->make();
			$item->setAllFillable(true);
			$item->setUsername($username);
			$item->setEmail($email);
			$item->setPreferences($preferences);
			$item->setPassword($password);
			$item->setGroup($users->getReference((int) $this->option('user')));
			$users->persist($item);

			$this->info('User Created');
		} catch(InvalidEntityException $e) {
			$this->error($e->getErrors()->first());
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return [
			['name', InputArgument::REQUIRED, 'Name of User.']
		];
	}

	protected function getOptions()
	{
		return [
			['group', null, InputOption::VALUE_REQUIRED, 'The group this user should belong to']
		];
	}

}
