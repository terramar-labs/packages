<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Nice\Security\AuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendController
{
    /**
     * @var bool If true, require HTTP Basic authentication
     */
    private $secure = true;
    /**
     * @var AuthenticatorInterface
     */
    private $authenticator;
    /**
     * @var string
     */
    private $outputDir;
    /**
     * @var string
     */
    private $basePath;

    /**
     * Constructor
     *
     * @param array $config
     * @param AuthenticatorInterface $authenticator
     */
    public function __construct(array $config, AuthenticatorInterface $authenticator)
    {
        $this->secure = isset($config['secure_satis']) ? (bool)$config['secure_satis'] : true;
        $this->outputDir = $config['output_dir'];
        $this->basePath = $config['base_path'];
        $this->authenticator = $authenticator;
    }

    /**
     * Handles packages.json and include/*.json requests.
     *
     * If secure_satis is enabled, HTTP Basic authentication will be required.
     * The username and password required are those defined in config.yml.
     *
     * @param Request $request
     * @return Response
     */
    public function outputAction(Request $request)
    {
        if ($this->secure) {
            $username = $request->getUser();
            $password = $request->getPassword();
            if (
                $this->authenticator->authenticate(new Request(['username' => $username, 'password' => $password])) !== true
            ) {
                return new Response('', 401, ['WWW-Authenticate' => 'Basic realm="'.$this->basePath.'"']);
            }
        }

        return new Response(
            file_get_contents($this->outputDir.urldecode($request->getPathInfo())),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']);
    }
}
