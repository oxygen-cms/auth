<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Lab404\Impersonate\Services\ImpersonateManager;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Notifications\UserInvitedNotification;
use Oxygen\Core\Controller\Controller;
use Oxygen\Core\Http\Notification;
use Oxygen\Crud\Controller\BasicCrudApi;
use Oxygen\Crud\Controller\SoftDeleteCrudApi;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;
use Webmozart\Assert\Assert;

class UsersController extends Controller {

    use BasicCrudApi, SoftDeleteCrudApi {
        SoftDeleteCrudApi::deleteDeleteApi insteadof BasicCrudApi;
        // we don't care about filtering by deleted status here
        BasicCrudApi::getListQueryParameters insteadof SoftDeleteCrudApi;
    }

    const PER_PAGE = 50;
    const INITIAL_RANDOM_PASSWORD_LENGTH = 50;

    const LANG_MAPPINGS = [
        'resource' => 'User',
        'pluralResource' => 'Users'
    ];
    private UserRepositoryInterface $repository;

    /**
     * Constructs the PagesController.
     *
     * @param UserRepositoryInterface                    $repository
     */
    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
        BasicCrudApi::setupLangMappings(self::LANG_MAPPINGS);
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function getInfoApiBasic(User $user): JsonResponse {
        return response()->json([
            'item' => $user->toArrayPublic()
        ]);
    }

    /**
     * Creates a new Resource - returns JSON response.
     *
     * @return JsonResponse
     * @throws \Exception|InvalidEntityException
     */
    public function postCreateApi(Request $request, PasswordBroker $passwordBroker) {
        $item = $this->repository->make();
        $item->fromArray($request->except(['_token']));
        $item->setPassword(Str::random(self::INITIAL_RANDOM_PASSWORD_LENGTH));
        $item->setPreferences([]);
        $resetToken = $passwordBroker->createToken($item);
        $this->repository->persist($item);
        \Illuminate\Support\Facades\Notification::send([$item], new UserInvitedNotification($resetToken));

        return response()->json([
            'status' => Notification::SUCCESS,
            'content' => trans('oxygen/auth::messages.accountCreated', ['email' => $item->getEmail()]),
            'item' => $item->toArray()
        ]);
    }

    /**
     * Change the user's password.
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidEntityException
     */
    public function putUpdateFullName(User $user, Request $request) {
        $user->setFullName($request->get('fullName'));
        $this->repository->persist($user);

        return response()->json([
            'content' => __('oxygen/auth::messages.fullNameChanged'),
            'status' => Notification::SUCCESS,
            'item' => $user->toArray()
        ]);
    }

    /**
     * Deletes an entity.
     *
     * @param int $item the item
     * @return JsonResponse
     * @throws InvalidEntityException
     */
    public function deleteDeleteApi($item) {
        $user = $this->repository->find($item);
        $user->delete();
        $this->repository->persist($user);

        return response()->json([
            'content' => __('oxygen/crud::messages.basic.deleted'),
            'status' => Notification::SUCCESS
        ]);
    }

    /**
     * Deletes a user permanently.
     * @param int $item
     * @return JsonResponse
     */
    public function deleteForce($item) {
        $user = $this->repository->find($item);
        $this->repository->delete($user);
        return response()->json([
            'content' => __('oxygen/auth::messages.account.terminated'),
            'status' => Notification::SUCCESS
        ]);
    }

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
        Assert::isInstanceOf($otherUser, User::class);
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
            Assert::isInstanceOf($user, User::class);
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
