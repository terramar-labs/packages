<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Sami;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    public function editAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')->findOneBy([
            'package' => $id,
        ]);

        return new Response($app->get('twig')->render('Plugin/Sami/edit.html.twig', [
            'config' => $config,
        ]));
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $config = $entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')->findOneBy([
            'package' => $id,
        ]);

        $config->setEnabled($request->get('sami_enabled') ? true : false);
        $config->setTitle($request->get('sami_title'));
        $config->setTheme($request->get('sami_theme'));
        $config->setTemplatesDir($request->get('sami_templates_dir'));
        $config->setRemoteRepoPath($request->get('sami_remote_repo_path'));
        $config->setTags($request->get('sami_tags'));
        $config->setRefs($request->get('sami_refs'));
        $config->setDocsPath($request->get('sami_docs_path'));

        $entityManager->persist($config);

        return new Response();
    }
}
