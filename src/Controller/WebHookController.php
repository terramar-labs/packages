<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Terramar\Packages\Event\PackageUpdateEvent;
use Terramar\Packages\Events;

class WebHookController
{
    public function receiveAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->findOneBy(array('id' => $id, 'enabled' => true));
        if (!$package || !$package->isEnabled()) {
            return new Response('Project not found', 404);
        }

        $receivedData = json_decode($request->getContent());
        if (!is_object($receivedData) || $package->getExternalId() != $receivedData->project_id) {
            return new Response('Project identifier does not match', 400);
        }

        $event = new PackageUpdateEvent($package, $receivedData);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = $app->get('event_dispatcher');
        $dispatcher->dispatch(Events::PACKAGE_UPDATE, $event);

        return new Response('Accepted', 202);
    }
}
