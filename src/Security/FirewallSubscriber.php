<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Security;

use Nice\Security\AuthenticatorInterface;
use Nice\Security\Event\SecurityEvent;
use Nice\Security\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles Authentication
 */
class FirewallSubscriber implements EventSubscriberInterface
{
    const SUCCESS_REDIR_SESSION_KEY = '__redirect';

    /**
     * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
     */
    private $firewallMatcher;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
     */
    private $authMatcher;

    /**
     * @var string
     */
    private $loginPath;

    /**
     * @var string
     */
    private $successPath;

    /**
     * @var string
     */
    private $tokenKey;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
     */
    private $logoutMatcher;

    /**
     * @var AuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestMatcherInterface  $firewallMatcher
     * @param RequestMatcherInterface  $authMatcher
     * @param RequestMatcherInterface  $logoutMatcher
     * @param AuthenticatorInterface   $authenticator
     * @param string                   $loginPath
     * @param string                   $successPath
     * @param string                   $tokenKey
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RequestMatcherInterface $firewallMatcher,
        RequestMatcherInterface $authMatcher,
        RequestMatcherInterface $logoutMatcher,
        AuthenticatorInterface $authenticator,
        $loginPath,
        $successPath,
        $tokenKey
    ) {
        $this->firewallMatcher = $firewallMatcher;
        $this->authMatcher = $authMatcher;
        $this->logoutMatcher = $logoutMatcher;
        $this->loginPath = $loginPath;
        $this->successPath = $successPath;
        $this->tokenKey = $tokenKey;
        $this->authenticator = $authenticator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if ($this->authMatcher->matches($request)) {
            $this->handleAuthentication($event);

            return;
        }

        if ($this->logoutMatcher->matches($request)) {
            $this->handleLogout($event);

            return;
        }

        if (!$this->firewallMatcher->matches($request)) {
            return;
        }

        if (!$request->hasSession()) {
            $event->setResponse(new Response('', 403));

            return;
        }

        if (!$request->getSession()->has($this->tokenKey)) {
            $this->redirectForAuthentication($event);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 8),
        );
    }

    private function handleAuthentication(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session) {
            $event->setResponse(new Response('', 403));

            return;
        }

        if ($this->authenticator->authenticate($request)) {
            $session->set($this->tokenKey, true);

            $successEvent = new SecurityEvent($request);
            $this->eventDispatcher->dispatch(Events::LOGIN_SUCCESS, $successEvent);

            $successPath = $request->getSession()->get(self::SUCCESS_REDIR_SESSION_KEY, $this->successPath);
            if ($successPath === $this->loginPath || $successPath === '/logout') {
                // TODO: The '/logout' check is a hack, should probably inject the configuration value.
                $successPath = $this->successPath;
            }
            $request->getSession()->remove(self::SUCCESS_REDIR_SESSION_KEY);
            $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl().$successPath));
        } else {
            $failEvent = new SecurityEvent($request);
            $this->eventDispatcher->dispatch(Events::LOGIN_FAIL, $failEvent);

            $this->redirectForAuthentication($event);
        }
    }

    private function redirectForAuthentication(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $request->getSession()->set(self::SUCCESS_REDIR_SESSION_KEY, $request->getPathInfo());
        $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl().$this->loginPath));
    }

    private function handleLogout(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $session->remove($this->tokenKey);

        $logoutEvent = new SecurityEvent($request);
        $this->eventDispatcher->dispatch(Events::LOGOUT, $logoutEvent);

        $this->redirectForAuthentication($event);
    }
}
