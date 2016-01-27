<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\CloneProject;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    public function editAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\CloneProject\PackageConfiguration')->findOneBy(array(
                'package' => $id,
            ));

        return new Response($app->get('twig')->render('Plugin/CloneProject/edit.html.twig', array(
                    'config' => $config,
                )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\CloneProject\PackageConfiguration')->findOneBy(array(
                'package' => $id,
            ));

        $config->setEnabled($request->get('cloneproject_enabled') ? true : false);
        $entityManager->persist($config);

        return new Response();
    }
}
