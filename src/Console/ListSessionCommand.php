<?php

namespace Oxygen\Auth\Console;

use Doctrine\ORM\EntityManager;
use Oxygen\Auth\Entity\DoctrineSession;
use Oxygen\Auth\Entity\Group;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Core\Console\Command;
use Symfony\Component\Console\Helper\Table;

class ListSessionCommand extends Command {

    /**
     * @var string lists current user sessions
     */
    protected $signature = 'session:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all sessions associated with the application';

    private $headers = [
        'Id',
        'User',
        'User Agent',
        'IP Address',
        'Last Activity'
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
     * @param EntityManager $entityManager
     */
    public function handle(EntityManager $entityManager) {
        $sessions = $entityManager->createQuery('SELECT o FROM ' . DoctrineSession::class . ' o')->getResult();
        $sessionRows = array_map(function(DoctrineSession $session) {
            $user = $session->getUser();
            return [
                'id' => $session->getId(),
                'user' => $user ? ($user->getFullName() . ' (' . $user->getEmail() . ')') : '-',
                'userAgent' => $session->getUserAgent(),
                'ipAddress' => $session->getIpAddress(),
                'lastActivity' => $session->getLastActivity()->format(self::DATE_FORMAT),
            ];
        }, $sessions);

        $generalTable = new Table($this->output);
        $generalTable->setStyle('box-double');
        $generalTable->setHeaders($this->headers);
        $generalTable->setRows($sessionRows);
        $generalTable->render();
    }

}
