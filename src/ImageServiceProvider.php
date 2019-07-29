<?php

namespace MgSoftware\Image;

use Illuminate\Support\ServiceProvider;

class ImageServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        // php artisan vendor:publish --tag=image-migrations
        $this->_publishMigrations();
        // php artisan vendor:publish --tag=image-models
        $this->_publishModels();
    }

    private function _publishMigrations()
    {
        $this->publishes([
            __DIR__.'/migrations/' => database_path('migrations')
        ], ['migrations', 'image-migrations']);
    }

    private function _publishModels()
    {
        $this->publishes([
            __DIR__.'/models/' => 'app/Models'
        ], ['image-models']);
    }
}