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
        $packagesJson = $rootDir . '/web/packages.json';
        $mtime = null;
        if (file_exists($packagesJson)) {
            $mtime = new \DateTime('@' . filemtime($packagesJson));
        }

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
