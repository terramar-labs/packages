<?php

namespace Terramar\Packages\Plugin\GitHub;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    public function newAction(Application $app, Request $request)
    {
        return new Response($app->get('twig')->render('Plugin/GitHub/new.html.twig'));
    }

    public function createAction(Application $app, Request $request)
    {
        $remote = $request->get('remote');
        if ($remote->getAdapter() !== 'GitHub') {
            return new Response();
        }

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = new RemoteConfiguration();
        $config->setRemote($remote);
        $config->setToken($request->get('github_token'));
        $config->setUsername($request->get('github_username'));
        $config->setEnabled($remote->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }

    public function editAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\GitHub\RemoteConfiguration')->findOneBy(array(
            'remote' => $id
        ));

        return new Response($app->get('twig')->render('Plugin/GitHub/edit.html.twig', array(
            'config' => $config ?: new RemoteConfiguration(),
        )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\GitHub\RemoteConfiguration')->findOneBy(array(
            'remote' => $id
        ));

        if (!$config) {
            return new Response();
        }

        $config->setToken($request->get('github_token'));
        $config->setUsername($request->get('github_username'));
        $config->setEnabled($config->getRemote()->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }
}
