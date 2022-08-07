<?php

namespace Wychoong\ViewExtract\Commands;

use Exception;
use Illuminate\Console\Command;
use Wychoong\ViewExtract\Commands\Concerns\ManageView;

class ExtractView extends Command
{

    use ManageView;

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
        $this->info(($this->canCache() ? 'Cache mode' : 'No cache mode') . PHP_EOL);

        $view = $this->argument('view');

        try {
            $found = $this->find($view);
            $this->info('Found: ' . $found);
        } catch (Exception $e) {
            $this->warn("{$view} view not found");

            return Command::INVALID;
        }

        if (!$this->checkFileExist()) {
            $this->warn("{$view} view not found");

            return Command::FAILURE;
        }

        try {
            if (!$this->option('force')) {
                $skip = false;
                if (!$this->checkPackageViewChanged()) {
                    $skip = $this->confirm("Package's view no changes, skip?", true);
                }

                if ($skip || !$this->confirm('View exist in app, overwrite?', false)) {
                    return Command::SUCCESS;
                }
            }
        } catch (Exception $e) {
            return Command::FAILURE;
        }

        $this->copyView();

        return Command::SUCCESS;
    }
}
