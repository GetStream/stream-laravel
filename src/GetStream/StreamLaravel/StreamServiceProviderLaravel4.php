<?php

namespace GetStream\StreamLaravel;

use Illuminate\Support\ServiceProvider;

class StreamServiceProviderLaravel4 extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('get-stream/stream-laravel');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['feed_manager'] = $this->app->share(function ($app) {
            $config = $app['config']->get('stream-laravel::config');

            $managerClass = $config['feed_manager_class'];
            $key = $config['api_key'];
            $secret = $config['api_secret'];

            return new $managerClass($key, $secret, $app['config']);
        });
    }
}
