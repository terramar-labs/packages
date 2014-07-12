<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebHookController
{
    public function receiveAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->findOneBy(array('id' => $id, 'enabled' => true));
        if (!$package) {
            return new Response('Forbidden', 403);
        }

        $receivedData = json_decode($request->getContent());
        if ($package->getExternalId() != $receivedData->project_id) {
            return new Response('Project identifier does not match', 400);
        }

        /** @var \Terramar\Packages\Helper\ResqueHelper $helper */
        $helper = $app->get('packages.helper.resque');
        $helper->enqueueOnce('default', 'Terramar\Packages\Job\UpdateAndBuildJob');

        return new Response('Accepted', 202);
    }
}
