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
		// $this->registerResources();

		$this->loadViewsFrom(__DIR__.'/../../views', 'stream-laravel');

		$this->publishes([
		    __DIR__.'/../../config/config.php' => config_path('getstream.php'),
		    __DIR__.'/../../views' => base_path('resources/views/vendor/stream-laravel'),
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__.'/../../config/config.php', 'getstream'
		);

		$this->app['feed_manager'] = $this->app->share(function($app)
        {

        	$manager_class = config('getstream.feed_manager_class');
        	$api_key = config('getstream.api_key');
        	$api_secret = config('getstream.api_secret');

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
