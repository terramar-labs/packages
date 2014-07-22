<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Entity\Configuration;

class ConfigurationController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        
        $configurations = $entityManager->getRepository('Terramar\Packages\Entity\Configuration')->findAll();
        
        return new Response($app->get('twig')->render('Configuration/index.html.twig', array(
                    'configurations' => $configurations
                )));
    }
    
    public function newAction(Application $app)
    {
        $adapters = $app->get('packages.helper.sync')->getAdapters();
        
        return new Response($app->get('twig')->render('Configuration/new.html.twig', array(
                    'adapters'      => $adapters,
                    'configuration' => new Configuration()
                )));
    }

    public function createAction(Application $app, Request $request)
    {
        $configuration = new Configuration();
        $configuration->setName($request->get('name'));
        $configuration->setUrl($request->get('url'));
        $configuration->setToken($request->get('token'));
        $configuration->setAdapter($request->get('adapter'));
        $configuration->setEnabled($request->get('enabled', false));

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $entityManager->persist($configuration);
        $entityManager->flush();
        
        return new RedirectResponse($app->get('router.url_generator')->generate('manage_configurations'));
    }

    public function editAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $configuration = $entityManager->getRepository('Terramar\Packages\Entity\Configuration')->find($id);
        if (!$configuration) {
            throw new \RuntimeException('Oops');
        }

        return new Response($app->get('twig')->render('Configuration/edit.html.twig', array(
                'configuration' => $configuration
            )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $configuration = $entityManager->getRepository('Terramar\Packages\Entity\Configuration')->find($id);
        $configuration->setName($request->get('name'));
        $configuration->setUrl($request->get('url'));
        $configuration->setToken($request->get('token'));
        $configuration->setEnabled($request->get('enabled', false));

        $entityManager->persist($configuration);
        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_configurations'));
    }

    public function syncAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $configuration = $entityManager->getRepository('Terramar\Packages\Entity\Configuration')->find($id);
        if (!$configuration) {
            throw new \RuntimeException('Oops');
        }

        /** @var \Terramar\Packages\Helper\SyncHelper $helper */
        $helper = $app->get('packages.helper.sync');
        $packages = $helper->synchronizePackages($configuration);
        foreach($packages as $package) {
            $entityManager->persist($package);
        }

        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_configurations'));
    }
}
