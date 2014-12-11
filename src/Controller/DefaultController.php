<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Controller;

use Nice\Application;
use Nice\Security\AuthenticationFailureSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class DefaultController
{
    public function indexAction(Application $app, Request $request)
    {
        $rootDir = $app->getRootDir();
        $mtime = new \DateTime('@' . filemtime($rootDir . '/web/packages.json'));

        return new Response(
            $app->get('twig')->render('Default/index.html.twig', array(
                    'updatedAt' => $mtime
                )));
    }

    public function loginAction(Application $app, Request $request)
    {
        $error = false;
        $session = $request->getSession();
        if ($session->get(AuthenticationFailureSubscriber::AUTHENTICATION_ERROR)) {
            $error = true;
            $session->remove(AuthenticationFailureSubscriber::AUTHENTICATION_ERROR);
        }

        return new Response($app->get('twig')->render('Default/login.html.twig', array('error' => $error)));
    }
}
