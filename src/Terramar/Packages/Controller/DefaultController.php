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

        $config = Yaml::parse(file_get_contents($rootDir . '/config.yml'));

        return new Response(
            $app->get('twig')->render('index.html.twig', array(
                    'updatedAt' => $mtime,
                    'name'      => $config['satis']['name']
                )));
    }

    public function loginAction(Application $app, Request $request)
    {
        $config = Yaml::parse(file_get_contents($app->getRootDir() . '/config.yml'));

        return new Response(
            $app->get('twig')->render('login.html.twig', array(
                    'name'      => $config['satis']['name']
                )));
    }
}
