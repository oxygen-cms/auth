<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;
use Oxygen\Core\Controller\Controller;
use Oxygen\Core\Http\Notification;
use Oxygen\Crud\Controller\BasicCrudApi;
use Oxygen\Crud\Controller\SoftDeleteCrudApi;th
use Oxygen\Auth\Repository\UserRepositoryInterface;

class UsersController extends Controller {

    use BasicCrudApi, SoftDeleteCrudApi {
        SoftDeleteCrudApi::deleteDeleteApi insteadof BasicCrudApi;
        SoftDeleteCrudApi::getListQueryParameters insteadof BasicCrudApi;
    }

    const LANG_MAPPINGS = [
        'resource' => 'User',
        'pluralResource' => 'Users'
    ];

    /**
     * Constructs the PagesController.
     *
     * @param UserRepositoryInterface                    $repository
     */
    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
        BasicCrudApi::setupLangMappings(self::LANG_MAPPINGS);
    }

//    /**
//     * Checks to see if the passed parameter was an instance
//     * of Model, if not it will run a query for the model.
//     *
//     * @param mixed $item
//     * @return object
//     */
//    protected function getItem($item) {
//        if(is_object($item)) {
//            $item->setAllFillable(true);
//            return $item;
//        } else {
//            $item = $this->repository->find($item);
//            $item->setAllFillable(true);
//            return $item;
//        }
//    }

//    /**
//     * Shows the create form.
//     *
//     * @return \Illuminate\View\View
//     */
//    public function getCreate() {
//        $extraFields = [];
//
//        $password = new FieldMetadata('password', 'password', true);
//        $field = new EditableField($password);
//
//        $extraFields[] = new Row([new Label($password), $field]);
//
//        return view('oxygen/crud::basic.create', [
//            'item' => $this->repository->make(),
//            'title' => __('oxygen/crud::ui.resource.create'),
//            'fields' => $this->crudFields,
//            'extraFields' => $extraFields
//        ]);
//    }

//    /**
//     * Creates a new Resource.
//     *
//     * @param Request $input
//     * @return \Illuminate\Http\Response
//     * @throws \Exception
//     */
//    public function postCreate(Request $input) {
//        try {
//            $item = $this->getItem($this->repository->make());
//            $item->fromArray($this->transformInput($input->except(['_method', '_token', 'password'])));
//            $item->setPassword($input->get('password'));
//            $this->repository->persist($item);
//
//            return notify(
//                new Notification(__('oxygen/crud::messages.basic.created')),
//                ['redirect' => $this->blueprint->getRouteName('getList')]
//            );
//        } catch(InvalidEntityException $e) {
//            return notify(
//                new Notification($e->getErrors()->first(), Notification::FAILED),
//                ['input' => true]
//            );
//        }
//    }

    /**
     * Logs in as the specified user.
     *
     * @param $id
     * @param Guard $auth
     * @param ImpersonateManager $manager
     * @return \Illuminate\Http\Response
     */
    public function postImpersonate($id, Guard $auth, ImpersonateManager $manager) {
        $otherUser = $this->getItem($id);
        if($auth->user() === $otherUser) {
            return notify(
                new Notification(__('oxygen/mod-auth::messages.cannotImpersonateSameUser'), Notification::FAILED),
            );
        }
        $manager->take($auth->user(), $otherUser);
        return notify(
            new Notification(__('oxygen/mod-auth::messages.impersonated', ['name' => $otherUser->getFullName()])),
            ['redirect' => 'dashboard.main', 'hardRedirect' => true]
        );
    }

    /**
     * @param Guard $auth
     * @param ImpersonateManager $manager
     * @return \Illuminate\Http\Response
     */
    public function postLeaveImpersonate(Guard $auth, ImpersonateManager $manager) {
        if($manager->isImpersonating()) {
            $manager->leave();

            return notify(
                new Notification(__('oxygen/mod-auth::messages.impersonationStopped', ['name' => $auth->user()->getFullName()])),
                ['refresh' => true]
            );
        } else {
            return notify(
                new Notification(__('oxygen/mod-auth::messages.notImpersonating'), Notification::FAILED),
            );
        }


    }

}
