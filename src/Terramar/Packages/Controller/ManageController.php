<?php

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Terramar\Packages\Repository\PackageRepository;

class ManageController
{
    public function indexAction(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $packages = $entityManager->getRepository('Terramar\Packages\Entity\Package')->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()->getSingleScalarResult();

        return new Response($app->get('twig')->render('Manage/index.html.twig', array(
                    'commits' => 11094,
                    'packages' => $packages,
                    'releases' => 325,
                )));
    }
}
