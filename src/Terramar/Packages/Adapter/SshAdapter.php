<?php

namespace Terramar\Packages\Adapter;

/**
 * SSH Repository adapter
 *
 * A very slow implementation that can list git repositories over an ssh connection
 */
class SshAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    private $uri;

    /**
     * Constructor
     *
     * @param $uri
     */
    public function __construct($uri)
    {
        $this->path = $uri;
    }

    /**
     * Returns a list of files in the given remote directory
     *
     * @return array
     */
    private function listFiles()
    {
        $res = popen('ssh ' . $this->path . ' ls', 'r');
        $files = explode("\n", stream_get_contents($res));
        fclose($res);

        return $files;
    }

    /**
     * Returns true if the file is a bare git repository on the remote server
     *
     * @param string $file
     *
     * @return bool
     */
    private function isRepo($file)
    {
        $res = popen('ssh ' . $this->path . ' \'if [ -f "' . $file . '/HEAD" ]; then echo "yes"; else echo "no"; fi;\'', 'r');
        $result = stream_get_contents($res);
        fclose($res);

        return 'yes' === trim($result);
    }

    /**
     * Gets a list of repositories
     *
     * @return array
     */
    public function getRepositories()
    {
        $repositories = array();

        $files = $this->listFiles();
        foreach ($files as $file) {
            if ($this->isRepo($file)) {
                $repositories[] = $file;
            }
        }

        return $repositories;
    }
}
