<?php

namespace Wychoong\ViewExtract\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\View\FileViewFinder;
use Wychoong\ViewExtract\Commands\Concerns\ManageView;

class SyncViews extends Command
{
    use ManageView;

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
        $this->info(($this->canCache() ? 'Cache mode' : 'No cache mode'));

        $this->exclude = config('view-extract.exclude', []);

        $this->only = config('view-extract.only', []);

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

        /** @var FileViewFinder $packageFinder */
        $packageFinder = $this->finder();

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
                    ->each(function ($file) use ($dryRun, $namespace, $packageFinder) {
                        try {
                            $view = "{$namespace}::{$file}";

                            $excluded = false;

                            $skip = false;

                            try {
                                $packageFile = $packageFinder->find($view);
                                $skip = !$this->checkHashChanged($packageFile);
                            } catch (Exception $e) {
                                $skip = true;
                            }

                            if (!$skip) {
                                if (filled($this->only) && !in_array($view, $this->only)) {
                                    $excluded = true;
                                }

                                if (!$excluded && in_array($view, $this->exclude)) {
                                    $excluded = true;
                                }
                            }

                            if ($dryRun) {
                                $this->components->twoColumnDetail($view, ($skip ? 'skipped' : ($excluded ? 'excluded' : '')));
                            // $this->line($view . ($skip ? "\t\t\t--skipped" : ($excluded ? "\t\t\t-- excluded" : '')));
                            } elseif ($skip) {
                                $this->line("Skip: {$view}");
                            } elseif ($excluded) {
                                $this->warn("Excluding: {$view}");
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

        $this->newLine();
        $this->canCache() && $this->line('* view that is not changed is skipped');
    }

    private function getDirContents($dir, $results = [])
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ($value != '.' && $value != '..') {
                $results = $this->getDirContents($path, $results);
            }
        }

        return $results;
    }
}
