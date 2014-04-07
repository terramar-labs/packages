<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Terramar\Packages\Model\Package;
use Terramar\Packages\Repository\PackageRepository;

class PackageController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var PackageRepository $repository */
        $repository = $app->get('repository.package');
        
        $packages = $repository->findAll();
        
        return new Response($app->get('twig')->render('Package/index.html.twig', array(
                    'packages' => $packages
                )));
    }
    
    public function newAction(Application $app)
    {
        return new Response($app->get('twig')->render('Package/new.html.twig', array(
                    'package' => new Package()
                )));
    }

    public function createAction(Application $app, Request $request)
    {
        $package = new Package();
        $package->setName($request->get('name'));
        $package->setDescription($request->get('description'));

        /** @var PackageRepository $repository */
        $repository = $app->get('repository.package');
        $repository->save($package);
        
        return new RedirectResponse('packages');
    }
}
