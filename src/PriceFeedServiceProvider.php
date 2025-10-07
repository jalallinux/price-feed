<?php

namespace JalalLinuX\PriceFeed;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PriceFeedServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name('price-feed')->hasConfigFile()->hasViews()->hasMigration('create_price_feed_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('price-feed', function ($app) {
            return new PriceFeedManager($app);
        });

        $this->app->alias('price-feed', PriceFeedManager::class);
    }
}
