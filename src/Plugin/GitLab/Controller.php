<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitLab;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    public function newAction(Application $app, Request $request)
    {
        return new Response($app->get('twig')->render('Plugin/GitLab/new.html.twig'));
    }

    public function createAction(Application $app, Request $request)
    {
        $remote = $request->get('remote');
        if ($remote->getAdapter() !== 'GitLab') {
            return new Response();
        }

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = new RemoteConfiguration();
        $config->setRemote($remote);
        $config->setFqnPrefix($request->get('fqn_prefix'));
        $config->setToken($request->get('gitlab_token'));
        $config->setUrl($request->get('gitlab_url'));
        $config->setEnabled($remote->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }

    public function editAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\GitLab\RemoteConfiguration')->findOneBy(array(
            'remote' => $id,
        ));

        return new Response($app->get('twig')->render('Plugin/GitLab/edit.html.twig', array(
            'config' => $config ?: new RemoteConfiguration(),
        )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\GitLab\RemoteConfiguration')->findOneBy(array(
            'remote' => $id,
        ));

        if (!$config) {
            return new Response();
        }

        $config->setFqnPrefix($request->get('gitlab_token'));
        $config->setToken($request->get('gitlab_token'));
        $config->setUrl($request->get('gitlab_url'));
        $config->setEnabled($config->getRemote()->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }
}
