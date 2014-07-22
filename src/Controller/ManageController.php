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
            ->where('p.enabled = true')
            ->getQuery()->setCacheable(true)->getSingleScalarResult();
        
        $remotes = $entityManager->getRepository('Terramar\Packages\Entity\Remote')->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.enabled = true')
            ->getQuery()->setCacheable(true)->getSingleScalarResult();

        return new Response($app->get('twig')->render('Manage/index.html.twig', array(
                    'packages' => $packages,
                    'remotes'  => $remotes,
                )));
    }
}
