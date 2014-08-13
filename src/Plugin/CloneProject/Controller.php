<?php

namespace Terramar\Packages\Plugin\CloneProject;

use Nice\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Entity\Remote;

class Controller
{
    public function editAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\CloneProject\PackageConfiguration')->findOneBy(array(
                'package' => $id
            ));

        return new Response($app->get('twig')->render('Plugin/CloneProject/edit.html.twig', array(
                    'config' => $config
                )));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\CloneProject\PackageConfiguration')->findOneBy(array(
                'package' => $id
            ));
        
        $config->setEnabled($request->get('cloneproject_enabled') ? true : false);
        $entityManager->persist($config);

        return new Response();
    }
}
