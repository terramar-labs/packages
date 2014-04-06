<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class ManageController
{
    public function indexAction(Application $app, Request $request)
    {
        return new Response($app->get('twig')->render('Manage/index.html.twig'));
    }
}
