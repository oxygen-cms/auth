<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Routing\Controller;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Core\Controller\BasicCrudTrait;
use Oxygen\Core\Controller\SoftDeleteCrudTrait;

class GroupsController extends Controller {

    use BasicCrudTrait, SoftDeleteCrudTrait {
        SoftDeleteCrudTrait::deleteDeleteApi insteadof BasicCrudTrait;
        // we don't care about filtering by deleted status here
        BasicCrudTrait::getListQueryParameters insteadof SoftDeleteCrudTrait;
    }

    const PER_PAGE = 50;

    const LANG_MAPPINGS = [
        'resource' => 'Group',
        'pluralResource' => 'Groups'
    ];

    protected GroupRepositoryInterface $repository; // used by the CRUD traits

    /**
     * Constructs the GroupsController.
     *
     * @param GroupRepositoryInterface                $repository
     */
    public function __construct(GroupRepositoryInterface $repository) {
        $this->repository = $repository;
        self::setupLangMappings(self::LANG_MAPPINGS);
    }

}
