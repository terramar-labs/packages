<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Entity\Remote;
use Terramar\Packages\Plugin\Actions;

class RemoteController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        
        $remotes = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->findAll();
        
        return new Response($app->get('twig')->render('Remote/index.html.twig', array(
                    'remotes' => $remotes
                )));
    }
    
    public function newAction(Application $app)
    {
        $adapters = $app->get('packages.helper.sync')->getAdapters();
        
        return new Response($app->get('twig')->render('Remote/new.html.twig', array(
                    'adapters'      => $adapters,
                    'remote' => new Remote()
                )));
    }

    public function createAction(Application $app, Request $request)
    {
        $remote = new Remote();
        $remote->setName($request->get('name'));
        $remote->setAdapter($request->get('adapter'));
        $remote->setEnabled($request->get('enabled', false));

        /** @var \Terramar\Packages\Helper\PluginHelper $helper */
        $helper = $app->get('packages.helper.plugin');
        $request->request->set('remote', $remote);
        $helper->invokeAction($request, Actions::REMOTE_CREATE, $request->request->all());

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $entityManager->persist($remote);
        $entityManager->flush();
        
        return new RedirectResponse($app->get('router.url_generator')->generate('manage_remotes'));
    }

    public function editAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $remote = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->find($id);
        if (!$remote) {
            throw new \RuntimeException('Oops');
        }

        return new Response($app->get('twig')->render('Remote/edit.html.twig', array(
                'remote' => $remote
            )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $remote = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->find($id);
        $remote->setName($request->get('name'));
        $remote->setEnabled($request->get('enabled', false));

        /** @var \Terramar\Packages\Helper\PluginHelper $helper */
        $helper = $app->get('packages.helper.plugin');
        $helper->invokeAction($request, Actions::REMOTE_UPDATE, array_merge($request->request->all(), array(
                'id' => $id
            )));

        $entityManager->persist($remote);
        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_remotes'));
    }

    public function syncAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $remote = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->find($id);
        if (!$remote) {
            throw new \RuntimeException('Oops');
        }

        /** @var \Terramar\Packages\Helper\SyncHelper $helper */
        $helper = $app->get('packages.helper.sync');
        $packages = $helper->synchronizePackages($remote);
        foreach($packages as $package) {
            $entityManager->persist($package);
        }

        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_remotes'));
    }
}
