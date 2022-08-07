<?php

namespace Wychoong\ViewExtract\Commands\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

trait CacheFileHash
{
    protected ?Filesystem $disk = null;

    private $cacheConfig = 'view-extract.cache';

    private $cacheFilename = 'cache.json';

    private function storage()
    {
        return $this->disk ??= Storage::build([
            'driver' => 'local',
            'root' => storage_path('view-extract'),
        ]);
    }

    protected function canCache()
    {
        return config($this->cacheConfig, true);
    }

    private function getFilenameHash($filename)
    {
        return md5($filename);
    }

    private function getFileHash($filename)
    {
        return md5_file($filename);
    }

    public function cacheFile($filename)
    {
        if (!$this->canCache()) return;

        $this->updateCache($filename);
    }

    public function checkHashChanged($filename): bool
    {
        if (!$this->canCache()) return true;

        $fileHash = $this->getFileHash($filename);
        $filenameHash = $this->getFilenameHash($filename);

        $cache = $this->getCache()[$filenameHash] ?? false;

        return $cache !== $fileHash;
    }

    private function updateCache($filename)
    {
        if (!$this->canCache()) return;

        $cache = $this->getCache();

        if (!$this->checkHashChanged($filename)) return;

        $cache[$this->getFilenameHash($filename)] = $this->getFileHash($filename);

        $this->storage()->put($this->cacheFilename, json_encode($cache, JSON_PRETTY_PRINT));
    }

    private function getCache(): array
    {
        if (!$this->canCache()) return [];

        if ($this->storage()->exists($this->cacheFilename)) {
            $file = $this->storage()->get($this->cacheFilename);

            return json_decode($file, true);
        }

        return [];
    }
}
