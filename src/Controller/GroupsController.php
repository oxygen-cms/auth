<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Routing\Controller;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use OxygenModule\Auth\Fields\GroupFieldSet;
use Oxygen\Crud\Controller\BasicCrudApi;
use Oxygen\Crud\Controller\SoftDeleteCrudApi;
use Webmozart\Assert\Assert;

class GroupsController extends Controller {

    use BasicCrudApi, SoftDeleteCrudApi {
        SoftDeleteCrudApi::deleteDeleteApi insteadof BasicCrudApi;
        // we don't care about filtering by deleted status here
        BasicCrudApi::getListQueryParameters insteadof SoftDeleteCrudApi;
    }

    const PER_PAGE = 50;

    const LANG_MAPPINGS = [
        'resource' => 'Group',
        'pluralResource' => 'Groups'
    ];

    private GroupRepositoryInterface $repository;

    /**
     * Constructs the GroupsController.
     *
     * @param GroupRepositoryInterface                $repository
     */
    public function __construct(GroupRepositoryInterface $repository) {
        $this->repository = $repository;
        Assert::isInstanceOf($this->repository, GroupRepositoryInterface::class);
        BasicCrudApi::setupLangMappings(self::LANG_MAPPINGS);
    }

}
