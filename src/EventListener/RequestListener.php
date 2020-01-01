<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RequestListener
{
    /** @var TokenStorage */
    protected $tokenStorage;

    private $openApiRoutes = [
        'api_login_check',
        'api_logout',
        'api_user_status'
    ];

    private $sessionUnlockRoutes = [
        'api_parsers_action',
        'api_parsers_mark_node'
    ];

    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param RequestEvent $event
     * @throws \ErrorException
     * @return RequestEvent
     */
    public function onKernelRequest(RequestEvent $event): RequestEvent
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        // checking csrf token for non-open api requests
        if ($event->isMasterRequest() && substr($routeName, 4) === 'api_' && !in_array($routeName, $this->openApiRoutes)) {
            if ($token = $this->tokenStorage->getToken()) {
                /** @var User $user */
                if ($user = $token->getUser()) {
                    $cookies = $event->getRequest()->cookies;
                    $csrfToken = $cookies->get('X-CSRF-TOKEN');

                    if (!$csrfToken || !($csrfToken === $user->getToken())) {
                        throw new \ErrorException('Method not allowed');
                    }
                }
            }
        }

        // prevent session lock for long-timed AJAX requests
        if ($request->isXmlHttpRequest() && in_array($routeName, $this->sessionUnlockRoutes)) {
            $session = $request->getSession();
            $session->save();
        }

        return $event;
    }
}