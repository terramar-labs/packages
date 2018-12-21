<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Bitbucket;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    public function newAction(Application $app, Request $request)
    {
        return new Response($app->get('twig')->render('Plugin/Bitbucket/new.html.twig'));
    }

    public function createAction(Application $app, Request $request)
    {
        $remote = $request->get('remote');
        if ($remote->getAdapter() !== 'Bitbucket') {
            return new Response();
        }

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = new RemoteConfiguration();
        $config->setRemote($remote);
        $config->setToken($request->get('bitbucket_token'));
        $config->setUsername($request->get('bitbucket_username'));
        $config->setAccount($request->get('bitbucket_account'));
        $config->setEnabled($remote->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }

    public function editAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\Bitbucket\RemoteConfiguration')->findOneBy([
            'remote' => $id,
        ]);

        return new Response($app->get('twig')->render('Plugin/Bitbucket/edit.html.twig', [
            'config' => $config ?: new RemoteConfiguration(),
        ]));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\Bitbucket\RemoteConfiguration')->findOneBy([
            'remote' => $id,
        ]);

        if (!$config) {
            return new Response();
        }

        $config->setToken($request->get('bitbucket_token'));
        $config->setUsername($request->get('bitbucket_username'));
	    $config->setAccount($request->get('bitbucket_account'));
	    $config->setEnabled($config->getRemote()->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }
}
