<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Twig;

use Symfony\Component\HttpFoundation\Request;

/**
 * The SecurityExtension provides helper functions for getting
 * Packages configuration values.
 */
class SecurityExtension extends \Twig_Extension
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('logged_in', function() {
                if ($this->request !== null) {
                    return $this->request->getSession()->get('__nice.is_authenticated', false);
                }
                return false;
            })
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'security';
    }

    /**
     * @param Request|null $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
}
