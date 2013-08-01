<?php

namespace Terramar\Packages\Adapter;

/**
 * Provides an adapter to list a GitLab installations projects
 */
class GitLabAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $key;

    /**
     * Constructor
     *
     * @param string $key  GitLab API key
     * @param string $host
     */
    public function __construct($key, $host)
    {
        $this->host = $host;
        $this->key  = $key;
    }

    /**
     * Gets a list of repositories
     *
     * @return array
     */
    public function getRepositories()
    {
        $client = new \Gitlab\Client($this->host);
        $client->authenticate($this->key, \Gitlab\Client::AUTH_URL_TOKEN);

        $projects = $client->api('projects')->all();

        return array_map(function($project) {
            $parts = explode(':', $project['ssh_url_to_repo']);

            return $parts[count($parts) - 1];
        }, $projects);
    }
}
