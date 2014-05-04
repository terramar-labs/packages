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
        
        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }

    public function editAction(Application $app, $id)
    {
        /** @var PackageRepository $repository */
        $repository = $app->get('repository.package');
        $package = $repository->findById($id);
        if (!$package) {
            throw new \RuntimeException('Oops');
        }
        
        return new Response($app->get('twig')->render('Package/edit.html.twig', array(
                'package' => $package
            )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var PackageRepository $repository */
        $repository = $app->get('repository.package');
        $package = $repository->findById($id);
        $package->setName($request->request->get('name'));
        $package->setDescription($request->request->get('description'));

        $repository->save($package);

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }
}
