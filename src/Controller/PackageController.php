<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Controller;

use Doctrine\ORM\EntityManager;
use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Plugin\Actions;

class PackageController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');

        $packages = $entityManager->getRepository('Terramar\Packages\Entity\Package')
            ->createQueryBuilder('p')
            ->join('p.remote', 'r', 'WITH', 'r.enabled = true')
            ->getQuery()->getResult();

        return new Response($app->get('templating')->render('Package/index.html.twig', [
            'packages' => $packages,
        ]));
    }

    public function editAction(Application $app, $id)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        /** @var Package $package */
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->find($id);
        if (!$package) {
            throw new NotFoundHttpException('Unable to locate Package');
        }

        $urlGenerator = $app->get('router.url_generator');

        return new Response($app->get('templating')->render('Package/edit.html.twig', [
            'package' => $package,
            'remotes' => $this->getRemotes($entityManager),
            'webhookUrl' => $urlGenerator->generate('webhook_receive', ['id' => $package->getId()], true)
        ]));
    }

    protected function getRemotes(EntityManager $entityManager)
    {
        return $entityManager->getRepository('Terramar\Packages\Entity\Remote')->findBy(['enabled' => true]);
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        /** @var Package $package */
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->find($id);
        if (!$package) {
            throw new NotFoundHttpException('Unable to locate Package');
        }

        $enabledBefore = $package->isEnabled();
        $enabledAfter = (bool)$request->get('enabled', false);

        $package->setName($request->request->get('name'));
        $package->setDescription($request->request->get('description'));
        $package->setEnabled($enabledAfter);

        if ($enabledBefore !== $enabledAfter) {
            $eventName = $enabledAfter ? Events::PACKAGE_ENABLE : Events::PACKAGE_DISABLE;
            $event = new PackageEvent($package);

            /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
            $dispatcher = $app->get('event_dispatcher');
            $dispatcher->dispatch($eventName, $event);
        }

        /** @var \Terramar\Packages\Helper\PluginHelper $helper */
        $helper = $app->get('packages.helper.plugin');
        $helper->invokeAction($request, Actions::PACKAGE_UPDATE, array_merge($request->request->all(), [
            'id' => $id,
        ]));

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
            throw new NotFoundHttpException('Unable to locate Package');
        }

        $enabledAfter = !$package->isEnabled();
        $package->setEnabled($enabledAfter);

        $eventName = $enabledAfter ? Events::PACKAGE_ENABLE : Events::PACKAGE_DISABLE;
        $event = new PackageEvent($package);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = $app->get('event_dispatcher');
        $dispatcher->dispatch($eventName, $event);

        $entityManager->persist($package);
        $entityManager->flush();

        return new RedirectResponse($app->get('router.url_generator')->generate('manage_packages'));
    }
}
