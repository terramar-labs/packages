<?php

namespace Terramar\Packages\Controller;

use Doctrine\ORM\EntityManager;
use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Job\UpdateAndBuildJob;

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
        if (!$package) {
            throw new \RuntimeException('Oops');
        }
        
        $enabledBefore = $package->isEnabled();
        $enabledAfter = (bool) $request->get('enabled', false);
        
        $package->setName($request->request->get('name'));
        $package->setDescription($request->request->get('description'));
        
        if ($enabledBefore !== $enabledAfter) {
            /** @var \Terramar\Packages\Helper\SyncHelper $helper */
            $helper = $app->get('packages.helper.sync');
            if ($enabledAfter) {
                $helper->enableHook($package);
            } else {
                $helper->disableHook($package);
            }
        }

        $entityManager->persist($package);
        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }

    public function toggleAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->find($id);
        if (!$package) {
            throw new \RuntimeException('Oops');
        }

        $enabledAfter = !$package->isEnabled();
        /** @var \Terramar\Packages\Helper\SyncHelper $helper */
        $helper = $app->get('packages.helper.sync');
        if ($enabledAfter) {
            $helper->enableHook($package);
        } else {
            $helper->disableHook($package);
        }

        $entityManager->persist($package);
        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }
    
    protected function getConfigurations(EntityManager $entityManager)
    {
        return $entityManager->getRepository('Terramar\Packages\Entity\Configuration')->findBy(array('enabled' => true)); 
    }
}
