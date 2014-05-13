<?php

namespace Terramar\Packages\Controller;

use Doctrine\ORM\EntityManager;
use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Entity\Package;

class PackageController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');

        $packages = $entityManager->getRepository('Terramar\Packages\Entity\Package')->findAll();
        
        return new Response($app->get('twig')->render('Package/index.html.twig', array(
                    'packages' => $packages
                )));
    }
    
    public function newAction(Application $app)
    {
        return new Response($app->get('twig')->render('Package/new.html.twig', array(
                    'package' => new Package(),
                    'configurations' => $this->getConfigurations($app->get('doctrine.orm.entity_manager'))
                )));
    }

    public function createAction(Application $app, Request $request)
    {
        $package = new Package();
        $package->setName($request->get('name'));
        $package->setDescription($request->get('description'));

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package->setConfiguration($entityManager->find('Terramar\Packages\Entity\Configuration', $request->get('configuration_id')));
        
        $entityManager->persist($package);
        $entityManager->flush();
        
        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }

    public function editAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->find($id);
        if (!$package) {
            throw new \RuntimeException('Oops');
        }
        
        return new Response($app->get('twig')->render('Package/edit.html.twig', array(
                'package' => $package,
                'configurations' => $this->getConfigurations($app->get('doctrine.orm.entity_manager'))
            )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->find($id);
        $package->setName($request->request->get('name'));
        $package->setDescription($request->request->get('description'));
        $package->setConfiguration($entityManager->find('Terramar\Packages\Entity\Configuration', $request->get('configuration_id')));

        $entityManager->persist($package);
        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }
    
    protected function getConfigurations(EntityManager $entityManager)
    {
        return $entityManager->getRepository('Terramar\Packages\Entity\Configuration')->findBy(array('enabled' => true)); 
    }
}
