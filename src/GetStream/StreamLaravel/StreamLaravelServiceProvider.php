<?php

namespace GetStream\StreamLaravel;

use Illuminate\Support\ServiceProvider;

class StreamLaravelServiceProvider extends ServiceProvider
{
    /**
     * Actual provider
     *
     * @var \Illuminate\Support\ServiceProvider
     */
    protected $provider;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->provider = $this->getProvider();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        return $this->provider->boot();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        return $this->provider->register();
    }

    /**
     * Return ServiceProvider according to Laravel version
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    private function getProvider()
    {
        if (version_compare(\Illuminate\Foundation\Application::VERSION, '5.0', '<')) {
            $provider = '\GetStream\StreamLaravel\StreamServiceProviderLaravel4';
        } else {
            $provider = '\GetStream\StreamLaravel\StreamServiceProviderLaravel5';
        }

        return new $provider($this->app);
    }
}
