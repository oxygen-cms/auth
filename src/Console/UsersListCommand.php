<?php


namespace Oxygen\Auth\Console;

use Oxygen\Auth\Entity\AuthenticationLogEntry;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class UsersListCommand extends Command {

    /**
     * @var string name and signature of console command
     */
    protected $signature = 'user:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered users of the application';

    const USERS_HEADERS = [
        'Id',
        'Username',
        'Full Name',
        'Email',
        'Group',
        'Email Verified',
        'Two-Factor Auth Enabled',
        'Last Login',
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
     * @param UserRepositoryInterface $users
     */
    public function handle(UserRepositoryInterface $users) {
        $users = $users->all();
        $this->renderUsersTable($users, $this->output);
    }

    public static function renderUsersTable(array $users, OutputInterface $output) {
        $usersRows = array_map(function(User $user) {

            $authLog = $user->getAuthenticationLogEntries()->filter(function(AuthenticationLogEntry $element) {
                return $element->getType() === AuthenticationLogEntry::LOGIN_SUCCESS;
            })->first();

            $lastLogin = $authLog ? $authLog->getTimestamp()->format(self::DATE_FORMAT) : null;

            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'group' => $user->getGroup()->getName(),
                'emailVerified' => $user->hasVerifiedEmail() ? '<fg=green>Yes</>' : '-',
                'twoFactorAuth' => $user->hasTwoFactorEnabled() ? '<fg=green>Yes</>' : '-',
                'lastLogin' => $lastLogin,
                'createdAt' => $user->getCreatedAt()->format(self::DATE_FORMAT),
                'updatedAt' => $user->getUpdatedAt()->format(self::DATE_FORMAT)
            ];
        }, $users);

        $generalTable = new Table($output);
        $generalTable->setStyle('box-double');
        $generalTable->setHeaders(self::USERS_HEADERS);
        $generalTable->setRows($usersRows);
        $generalTable->render();
    }

}
