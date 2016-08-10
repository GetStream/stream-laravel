<?php

namespace GetStream\StreamLaravel;

use Illuminate\Support\ServiceProvider;

class StreamServiceProviderLaravel5 extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views', 'stream-laravel');

        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('stream-laravel.php'),
            __DIR__.'/../../views'             => base_path('resources/views/vendor/stream-laravel'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'stream-laravel');

        $this->app['feed_manager'] = $this->app->share(function ($app) {
            $config = $app['config']->get('stream-laravel');

            $managerClass = $config['feed_manager_class'];
            $key = $config['api_key'];
            $secret = $config['api_secret'];

            return new $managerClass($key, $secret, new Collection($config));
        });
    }
}
