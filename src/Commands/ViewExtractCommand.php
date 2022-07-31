<?php

namespace Wychoong\ViewExtract\Commands;

use Illuminate\Console\Command;

class ViewExtractCommand extends Command
{
    public $signature = 'laravel-view-extract';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
