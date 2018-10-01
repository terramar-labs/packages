<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Composer\Config;
use Composer\IO\ConsoleIO;
use Composer\Repository\ComposerRepository;
use Nice\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Controller\ContainerAwareController;

class InventoryController extends ContainerAwareController
{
    /**
     * Displays a list of available packages and versions.
     */
    public function indexAction(Application $app)
    {
        $repository = $this->getRepository();

        $contents = [];
        foreach ($repository->getPackages() as $package) {
            /* @var \Composer\Package\CompletePackage $package */
            $contents[$package->getName()]['name'] = $package->getName();
            $contents[$package->getName()]['versions'][] = $package->getPrettyVersion();
        }

        return new Response($app->get('templating')->render('Plugin/Satis/Inventory/index.html.twig', [
            'contents' => $contents,
        ]));
    }

    /**
     * Displays the details for a given package.
     */
    public function viewAction(Application $app, $id, $version = null)
    {
        $repository = $this->getRepository();

        $id = str_replace('+', '/', $id);
        if ($version) {
            $version = str_replace('+', '/', $version);
        }

        $packages = $repository->findPackages($id);

        usort($packages, function ($a, $b) {
            if ($a->getReleaseDate() > $b->getReleaseDate()) {
                return -1;
            }

            return 1;
        });

        $package = null;
        if ($version) {
            foreach ($packages as $p) {
                if ($p->getPrettyVersion() == $version) {
                    $package = $p;
                }
            }
        } else {
            /* @var \Composer\Package\CompletePackage $package */
            $package = $packages[0];
        }

        return new Response($app->get('templating')->render('Plugin/Satis/Inventory/view.html.twig', [
            'packages' => $packages,
            'package'  => $package,
        ]));
    }

    /**
     * @return \Composer\Repository\ComposerRepository
     */
    private function getRepository()
    {
        $configuration = $this->container->getParameter('packages.configuration');

        $io = new ConsoleIO(new ArgvInput([]), new ConsoleOutput(), new HelperSet([]));
        $config = new Config();
        $config->merge([
            'config' => [
                'home' => $this->container->getParameter('app.root_dir'),
            ],
        ]);
        $repository = new ComposerRepository([
            'url' => 'file://' . $configuration['output_dir'] . '/packages.json',
        ], $io, $config);

        return $repository;
    }
}
