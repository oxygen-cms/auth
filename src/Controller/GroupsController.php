<?php

namespace Oxygen\Auth\Controller;

use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\SoftDeleteCrudController;
use OxygenModule\Auth\Fields\GroupFieldSet;

class GroupsController extends SoftDeleteCrudController {

    /**
     * Constructs the PagesController.
     *
     * @param GroupRepositoryInterface                $repository
     * @param BlueprintManager                        $manager
     * @param \OxygenModule\Auth\Fields\GroupFieldSet $fields
     */
    public function __construct(GroupRepositoryInterface $repository, BlueprintManager $manager, GroupFieldSet $fields) {
        parent::__construct($repository, $manager, $fields);
    }

}
