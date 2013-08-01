<?php

namespace Terramar\Packages\Adapter;

class FileAdapter implements AdapterInterface
{
    private $path;

    public function __construct($path)
    {
        $this->path = realpath($path);

        if (!file_exists($this->path)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', $this->path));
        }
    }

    public function getRepositories()
    {
        $repositories = array();
        $it = new \DirectoryIterator($this->path);
        foreach ($it as $file) {
            $path = $file->getPathname();

            // TODO: Add support for non-bare repositories
            if (is_dir($path)
                && file_exists($path . '/HEAD')
            ) {
                $repositories[] = $path;
            }
        }

        return $repositories;
    }
}
