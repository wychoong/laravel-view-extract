<?php

namespace Wychoong\ViewExtract\Commands\Concerns;

use Exception;
use Illuminate\View\FileViewFinder;

trait ManageView
{
    use CacheFileHash;

    private ?FileViewFinder $finder = null;

    private string $sourcePath;

    private string $destination;

    private function getFinder(): FileViewFinder
    {
        /** @var FileViewFinder $finder */
        $finder = clone (app('view'))->getFinder();

        $appPath = resource_path('views/vendor');
        foreach ($finder->getHints() as $namespace => $paths) {
            $oriPaths = $paths;
            foreach ($paths as $index => $path) {
                if (strpos($path, $appPath) === 0) {
                    unset($paths[$index]);
                }
            }
            if ($oriPaths != $paths) {
                $finder->replaceNamespace($namespace, $paths);
            }
        }

        return $this->finder = $finder;
    }

    public function finder(): FileViewFinder
    {
        return $this->finder ?? $this->getFinder();
    }

    public function find($view)
    {
        $this->sourcePath = $this->finder()->find($view);

        $this->getFileDestination();

        return $this->sourcePath;
    }

    private function createDirectory()
    {
        if (!file_exists(dirname($this->destination))) {
            mkdir(dirname($this->destination), 0755, true);
        }
    }

    public function getFileDestination()
    {
        $needle = 'resources/views/';
        $pos = strrpos($this->sourcePath, $needle);
        if (!$pos) {
            $this->warn('unable to handle the source path');

            throw new Exception("error");
        }

        $subPos = $pos + strlen($needle);

        $namespace = '';

        foreach ($this->finder()->getHints() as $_namespace => $paths) {
            foreach ($paths as $path) {
                if (strpos($this->sourcePath, $path) === 0) {
                    $namespace = $_namespace . '/';
                    break;
                }
            }
            if ($namespace) {
                break;
            }
        }

        $sourceRelativePath = substr($this->sourcePath, $subPos);

        $this->destination = resource_path("views/vendor/{$namespace}{$sourceRelativePath}");
    }

    public function checkFileExist()
    {
        return file_exists($this->destination);
    }

    public function copyView()
    {
        $this->createDirectory();

        copy($this->sourcePath, $this->destination);

        $this->cacheFile($this->sourcePath);

        $this->info('Done extract view to ' . $this->destination);
    }

    public function checkPackageViewChanged()
    {
        return $this->checkHashChanged($this->sourcePath);
    }
}
