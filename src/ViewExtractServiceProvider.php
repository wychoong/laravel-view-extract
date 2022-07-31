<?php

namespace Wychoong\ViewExtract;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wychoong\ViewExtract\Commands\ExtractView;
use Wychoong\ViewExtract\Commands\SyncViews;

class ViewExtractServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-view-extract')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-view-extract_table')
            ->hasCommands([
                ExtractView::class,
                SyncViews::class,
            ]);
    }
}
