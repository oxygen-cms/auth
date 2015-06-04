<?php

namespace Oxygen\Auth\Filter;

use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Translation\Translator;
use Oxygen\Core\Support\Facades\Response;

use Oxygen\Core\Http\Notification;

class AuthFilter {

    /**
     * AuthManager dependency.
     *
     * @var AuthManager
     */

    protected $auth;

    /**
     * Config dependency.
     *
     * @var Config
     */

    protected $config;

    /**
     * Response facade.
     *
     * @var Response
     */

    protected $response;

    /**
     * Translator dependency
     *
     * @var Translator
     */

    protected $lang;

    /**
     * UrlGenerator dependency.
     *
     * @var UrlGenerator
     */

    protected $url;

    /**
     * Constructs the PermissionsFilter
     *
     * @param AuthManager       $auth       AuthManager instance
     * @param Config            $config     Config instance
     * @param Reponse           $redirect   Response facade
     * @param Translator        $lang       Translator instance
     * @param UrlGenerator      $url        UrlGenerator instance
     */
    public function __construct(AuthManager $auth, Config $config, Response $response, Translator $lang, UrlGenerator $url) {
        $this->auth     = $auth;
        $this->config   = $config;
        $this->response = $response;
        $this->lang     = $lang;
        $this->url      = $url;
    }

    /**
     * The `auth` filter is used to verify that the user
     * of the current session is logged into this application.
     *
     * @return Response
     */
    public function auth() {
        if($this->auth->guest()) {
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.notLoggedIn'), Notification::FAILED),
                ['redirect' => 'auth.getLogin']
            );
        }
    }

    /**
     * The `guest` filter is used to verify that the user
     * of the current session is not logged into this application.
     *
     * @return Response
     */
    public function guest() {
        if($this->auth->check()) {
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.alreadyLoggedIn')),
                ['redirect' => $this->config->get('oxygen/auth::dashboard')]
            );
        }
    }

}