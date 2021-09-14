<?php

namespace Oxygen\Auth\Console;

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

    protected $name = 'group:add';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Creates a new group.';

    /**
     * Execute the console command.
     *
     * @param \Oxygen\Auth\Repository\GroupRepositoryInterface $groups
     * @return mixed
     */
    public function handle(GroupRepositoryInterface $groups) {
        $name = $this->argument('name');

        $description = $this->option('description');

        $preferences = file_get_contents(__DIR__ . '/../../resources/seed/group_preferences.json');
        $permissions = file_get_contents(__DIR__ . '/../../resources/seed/group_permissions.json');

        try {
            $item = $groups->make();
            $item->setName($name);
            $item->setDescription($description);
            $item->setPreferences($preferences);
            $item->setPermissions($permissions);
            $groups->persist($item);

            $this->info('Group created with id ' . $item->getId());
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
            ['name', InputArgument::REQUIRED, 'Name of the group']
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['description', null, InputOption::VALUE_REQUIRED, 'Description of the group', '']
        ];
    }

}
