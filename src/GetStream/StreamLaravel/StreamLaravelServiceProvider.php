<?php namespace GetStream\StreamLaravel;

use Illuminate\Support\ServiceProvider;

class StreamLaravelServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

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
		$this->app['feed_manager'] = $this->app->share(function($app)
        {
        	$manager_class = $app['config']->get('stream-laravel::feed_manager_class');
        	$api_key = $app['config']->get('stream-laravel::api_key');
        	$api_secret = $app['config']->get('stream-laravel::api_secret');
            return new $manager_class($api_key, $api_secret, $app['config']);
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
