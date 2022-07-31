<?php

namespace Wychoong\ViewExtract\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\View\FileViewFinder;

class SyncViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:sync {namespace?} {--check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync extracted views from vendor';

    protected $exclude = [];

    protected $only = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->exclude = config('view-extract.exclude', []);

        $this->only = config('only', []);

        if ($this->option('check')) {
            $this->newLine();
            $this->line('##  Dry run mode  ##');
            $this->newLine();

            $this->syncAll(true);
        } elseif ($view = $this->argument('namespace')) {
            $this->syncView($view);
        } else {
            $this->syncAll();
        }

        $this->newLine();

        return 0;
    }

    private function syncView(string $view)
    {
        $this->call(ExtractView::class, ['view' => $view, '--force' => true]);
    }

    private function syncAll($dryRun = false)
    {
        /** @var FileViewFinder $finder */
        $finder = app('view')->getFinder();

        $namespaces = $finder->getHints();

        foreach ($namespaces as $namespace => $paths) {
            $vendorDir = resource_path("views/vendor/{$namespace}");

            if (file_exists($vendorDir)) {
                $this->info("Checking in: {$vendorDir}");

                $files = collect($this->getDirContents($vendorDir))
                    ->map(function ($file) use ($vendorDir) {
                        return $relativePath = Str::of($file)
                            ->after($vendorDir)
                            ->beforeLast('.blade.php')
                            ->explode('/')
                            ->filter(fn ($part) => filled($part))
                            ->join('.');
                    })
                    ->each(function ($file) use ($dryRun, $namespace) {
                        try {
                            $view = "{$namespace}::{$file}";

                            $excluded = false;

                            if (filled($this->only) && ! in_array($view, $this->only)) {
                                $excluded = true;
                            }

                            if (! $excluded && in_array($view, $this->exclude)) {
                                $excluded = true;
                            }

                            if ($dryRun) {
                                $this->line($view.($excluded ? "\t\t\t-- excluded" : ''));
                            } elseif ($excluded) {
                                $this->warn("Excluding: {$view}");

                                return;
                            } else {
                                $this->syncView($view);
                            }
                        } catch (Exception $e) {
                            $this->warn("Error: $file \tskipping");

                            return;
                        }
                    });
            }
        }
    }

    private function getDirContents($dir, $results = [])
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if (! is_dir($path)) {
                $results[] = $path;
            } elseif ($value != '.' && $value != '..') {
                $results = $this->getDirContents($path, $results);
            }
        }

        return $results;
    }
}
