<?php

namespace Oxygen\Auth\Filter;

use Illuminate\Auth\AuthManager;
use Illuminate\Translation\Translator;

use Oxygen\Core\Support\Facades\Response;
use Oxygen\Core\Http\Notification;

class PermissionsFilter {

    /**
     * AuthManager dependency.
     *
     * @var AuthManager
     */

    protected $auth;

    /**
     * Translator dependency.
     *
     * @var Translator
     */

    protected $lang;

    /**
     * Response facade.
     *
     * @var Response
     */

    protected $response;

    /**
     * Constructs the PermissionsFilter
     *
     * @param AuthManager $auth AuthManager instance
     * @param Translator $lang Translator instance
     * @param Response $response Response facade
     */

    public function __construct(AuthManager $auth, Translator $lang, Response $response) {
        $this->auth = $auth;
        $this->lang = $lang;
        $this->response = $response;
    }

    /**
     * The permissions filter checks that the logged in user has
     * the required priviledges to view the requested page.
     * If not, an error message will be displayed.
     *
     * @return Response
     */

    public function filter($route, $request, $value) {
        if(!$this->auth->user()->hasPermissions($value)) {
            $notification = new Notification(
                $this->lang->get('oxygen/auth::messages.permissions.noPermissions', ['permission' => $value]),
                Notification::FAILED
            );
            return $this->response->notification($notification, ['redirect' => 'dashboard.main']);
        }
    }

}