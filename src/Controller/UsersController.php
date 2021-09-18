<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Lab404\Impersonate\Services\ImpersonateManager;
use Oxygen\Auth\Entity\User;
use Oxygen\Core\Controller\Controller;
use Oxygen\Core\Http\Notification;
use Oxygen\Crud\Controller\BasicCrudApi;
use Oxygen\Crud\Controller\SoftDeleteCrudApi;
use Oxygen\Auth\Repository\UserRepositoryInterface;

class UsersController extends Controller {

    use BasicCrudApi, SoftDeleteCrudApi {
        SoftDeleteCrudApi::deleteDeleteApi insteadof BasicCrudApi;
        // we don't care about filtering by deleted status here
        BasicCrudApi::getListQueryParameters insteadof SoftDeleteCrudApi;
    }

    const PER_PAGE = 50;

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
     * @param int $otherUser
     * @param Guard $auth
     * @param ImpersonateManager $manager
     * @return JsonResponse
     */
    public function postImpersonate($otherUser, Guard $auth, ImpersonateManager $manager) {
        $otherUser = $this->repository->find($otherUser);
        if($auth->user() === $otherUser) {
            return response()->json(['content' => __('oxygen/auth::messages.cannotImpersonateSameUser'), 'status' => 'failed'], 400);
        }
        // we force the use of the `web` guard, otherwise the impersonation doesn't persist across requests for some reason
        $manager->take($auth->user(), $otherUser, 'web');
        return response()->json([
            'content' => __('oxygen/auth::messages.impersonated', ['name' => $otherUser->getFullName()]),
            'user' => $otherUser->toArray(),
            'impersonating' => true,
            'status' => 'success'
        ]);
    }

    /**
     * @param AuthManager $auth
     * @param ImpersonateManager $manager
     * @return JsonResponse
     */
    public function postLeaveImpersonate(AuthManager $auth, ImpersonateManager $manager) {
        if($manager->isImpersonating()) {
            $manager->leave();

            $user = $auth->guard('web')->user();
            return response()->json([
                'content' => __('oxygen/auth::messages.impersonationStopped', ['name' => $user->getFullName()]),
                'status' => 'success',
                'user' => $user->toArray(),
                'impersonating' => false
            ]);
        } else {
            return response()->json([
                'content' => __('oxygen/auth::messages.notImpersonating'),
                'status' => 'failed',
                'code' => 'not_impersonating'
            ], 400);
        }


    }

}
