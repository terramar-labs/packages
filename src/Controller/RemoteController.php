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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Terramar\Packages\Entity\Remote;
use Terramar\Packages\Event\RemoteEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Plugin\Actions;

class RemoteController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');

        $remotes = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->findAll();

        return new Response($app->get('templating')->render('Remote/index.html.twig', [
            'remotes' => $remotes,
        ]));
    }

    public function newAction(Application $app)
    {
        $adapters = $app->get('packages.helper.sync')->getAdapters();

        return new Response($app->get('templating')->render('Remote/new.html.twig', [
            'adapters' => $adapters,
            'remote'   => new Remote(),
        ]));
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
            throw new NotFoundHttpException('Unable to locate Remote');
        }

        return new Response($app->get('templating')->render('Remote/edit.html.twig', [
            'remote' => $remote,
        ]));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $remote = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->find($id);
        if (!$remote) {
            throw new NotFoundHttpException('Unable to locate Remote');
        }

        $remote->setName($request->get('name'));

        $enabledBefore = $remote->isEnabled();
        $enabledAfter = (bool)$request->get('enabled', false);

        $remote->setEnabled($enabledAfter);

        if ($enabledBefore !== $enabledAfter) {
            $eventName = $enabledAfter ? Events::REMOTE_ENABLE : Events::REMOTE_DISABLE;
            $event = new RemoteEvent($remote);

            /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
            $dispatcher = $app->get('event_dispatcher');
            $dispatcher->dispatch($eventName, $event);
        }

        /** @var \Terramar\Packages\Helper\PluginHelper $helper */
        $helper = $app->get('packages.helper.plugin');
        $helper->invokeAction($request, Actions::REMOTE_UPDATE, array_merge($request->request->all(), [
            'id' => $id,
        ]));

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
            throw new NotFoundHttpException('Unable to locate Remote');
        }

        /** @var \Terramar\Packages\Helper\SyncHelper $helper */
        $helper = $app->get('packages.helper.sync');
        $packages = $helper->synchronizePackages($remote);
        foreach ($packages as $package) {
            $entityManager->persist($package);
        }

        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_remotes'));
    }
}
