<?php

namespace MgSoftware\Image;

use Illuminate\Support\ServiceProvider;
use MgSoftware\Image\components\ImageComponent;

class ImageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('image', function($app) {
            return new ImageComponent();
        });
    }

    public function boot()
    {
        // php artisan vendor:publish --tag=mgsoftware-image

        // php artisan vendor:publish --tag=image-migrations
        $this->_publishMigrations();

        // php artisan vendor:publish --tag=image-config
        $this->_publishConfiguration();
    }

    private function _publishMigrations()
    {
        $this->publishes([
            __DIR__.'/migrations/' => database_path('migrations')
        ], ['migrations', 'mgsoftware-image', 'image-migrations']);
    }

    private function _publishConfiguration()
    {
        $this->publishes([
            __DIR__.'/config/' => config_path()
        ], ['config', 'mgsoftware-image', 'image-config']);
    }
}