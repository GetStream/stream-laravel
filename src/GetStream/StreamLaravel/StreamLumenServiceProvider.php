<?php

namespace GetStream\StreamLaravel;

use Illuminate\Support\ServiceProvider;

class StreamLumenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if (method_exists($this, 'publishes')) {
            $this->loadViewsFrom(__DIR__.'/../../views', 'stream-laravel');

            $this->publishes([
                __DIR__.'/../../config/config.php' => app()->configure('stream-laravel.php'),
                __DIR__.'/../../views' => base_path('resources/views/vendor/stream-laravel'),
            ]);
        } else {
            $this->package('get-stream/stream-laravel');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (method_exists($this, 'publishes')) {
            $this->registerResources();
        }

        $this->app->singleton('feed_manager', function($app) {
            $manager_class = config('stream-laravel.feed_manager_class');
            $api_key = config('stream-laravel.api_key');
            $api_secret = config('stream-laravel.api_secret');

            return new $manager_class($api_key, $api_secret, $this->app['config']);
        });
    }

    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $userConfigFile = app()->configure('stream-laravel.php');
        $packageConfigFile = __DIR__.'/../../config/config.php';
        $config = $this->app['files']->getRequire($packageConfigFile);

        if (file_exists($userConfigFile)) {
            $userConfig = $this->app['files']->getRequire($userConfigFile);
            $config = array_replace_recursive($config, $userConfig);
        } else {
            //only set if we dont have a config file for stream-laravel
            $namespace = 'stream-laravel.';
            foreach($config as $key => $value) {
                $this->app['config']->set($namespace . $key , $value);
            }
        }
    }
}
