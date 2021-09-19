<?php


namespace Oxygen\Auth\Console;

use Oxygen\Auth\Entity\Group;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Core\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ListGroupCommand extends Command {

    /**
     * @var string name and signature of console command
     */
    protected $signature = 'group:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all groups';

    const GROUP_HEADERS = [
        'Nickname',
        'Display Name',
        'Description',
        'Icon',
        '# Users',
        'Parent Group',
        'Child Groups',
        'Created At',
        'Updated At'
    ];

    const DATE_FORMAT = 'd M y, H:i:s T';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display users in a table.
     * @param GroupRepositoryInterface $groups
     */
    public function handle(GroupRepositoryInterface $groups) {
        $groups = $groups->all();
        self::renderUsersTable($groups, $this->output);
    }

    public static function renderUsersTable(array $groups, OutputInterface $output) {
        $groupRows = array_map(function(Group $group) {

            $parent = $group->getParent();

            return [
                'nickname' => $group->getNickname(),
                'name' => $group->getName(),
                'description' => $group->getDescription(),
                'icon' => $group->getIcon(),
                'users' => '<fg=blue>' . $group->getUsers()->count() . '</> members',
                'parent' => $parent ? $parent->getNickname() : '-',
                'children' => implode(', ' ,$group->getChildren()->map(function(Group $child) { return $child->getNickname(); })->toArray()),
                'createdAt' => $group->getCreatedAt() !== null ? $group->getCreatedAt()->format(self::DATE_FORMAT) : null,
                'updatedAt' => $group->getUpdatedAt() !== null ? $group->getUpdatedAt()->format(self::DATE_FORMAT) : null
            ];
        }, $groups);

        $generalTable = new Table($output);
        $generalTable->setStyle('box-double');
        $generalTable->setHeaders(self::GROUP_HEADERS);
        $generalTable->setRows($groupRows);
        $generalTable->render();
    }

}
