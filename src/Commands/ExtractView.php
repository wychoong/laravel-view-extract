<?php

namespace Wychoong\ViewExtract\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\View\FileViewFinder;

class ExtractView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:extract {view : namespace::foo.bar} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract view from vendor';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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

        $view = $this->argument('view');

        try {
            $found = $finder->find($view);
            $this->info('Found: '.$found);
        } catch (Exception $e) {
            $this->warn("{$view} view not found");

            return Command::INVALID;
        }

        $needle = 'resources/views/';
        $pos = strrpos($found, $needle);
        if (! $pos) {
            $this->warn('unable to handle the source path');

            return Command::FAILURE;
        }

        $subPos = $pos + strlen($needle);

        $namespace = '';

        foreach ($finder->getHints() as $_namespace => $paths) {
            foreach ($paths as $path) {
                if (strpos($found, $path) === 0) {
                    $namespace = $_namespace.'/';
                    break 2;
                }
            }
            if ($namespace) {
                break;
            }
        }

        $sourceRelativePath = substr($found, $subPos);

        $destination = resource_path("views/vendor/{$namespace}{$sourceRelativePath}");

        if (! file_exists(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        if (! $this->option('force') && file_exists($destination)) {
            if (! $this->confirm('View exist in app, overwrite?', false)) {
                return Command::SUCCESS;
            }
        }

        copy($found, $destination);

        $this->info('Done extract view to '.$destination);

        return Command::SUCCESS;
    }
}
