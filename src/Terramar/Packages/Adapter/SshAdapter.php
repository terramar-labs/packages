<?php

namespace Terramar\Packages\Adapter;

/**
 * SSH Repository adapter
 *
 * A very slow implementation that can list git repositories over an ssh connection
 */
class SshAdapter implements AdapterInterface
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    private function listFiles()
    {
        $res = popen('ssh ' . $this->path . ' ls', 'r');
        $files = explode("\n", stream_get_contents($res));
        fclose($res);

        return $files;
    }

    private function isRepo($file)
    {
        $res = popen('ssh ' . $this->path . ' \'if [ -f "' . $file . '/HEAD" ]; then echo "yes"; else echo "no"; fi;\'', 'r');
        $result = stream_get_contents($res);
        fclose($res);

        return 'yes' === trim($result);
    }

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
