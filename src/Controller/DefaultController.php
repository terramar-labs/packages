<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
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
        return new Response($app->get('twig')->render('Default/login.html.twig'));
    }
}
