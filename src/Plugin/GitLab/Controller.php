<?php

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

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = new RemoteConfiguration();
        $config->setRemote($remote);
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
            'remote' => $id
        ));

        return new Response($app->get('twig')->render('Plugin/GitLab/edit.html.twig', array(
            'config' => $config,
        )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\GitLab\RemoteConfiguration')->findOneBy(array(
            'remote' => $id
        ));

        $config->setEnabled($request->get('sami_enabled') ? true : false);
        $config->setToken($request->get('gitlab_token'));
        $config->setUrl($request->get('gitlab_url'));
        $config->setEnabled($config->getRemote()->isEnabled());

        $entityManager->persist($config);

        return new Response();
    }
}
