<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\Response;

class WebHookController
{
    public function receiveAction(Application $app, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->findBy(array('id' => $id, 'enabled' => true));
        if (!$package) {
            return new Response('Bad request', 400);
        }

        /** @var \Terramar\Packages\Helper\ResqueHelper $helper */
        $helper = $app->get('packages.helper.resque');
        $helper->enqueueOnce('default', 'Terramar\Packages\Job\UpdateAndBuildJob');

        return new Response('Accepted', 202);
    }
}
