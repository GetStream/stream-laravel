<?php

namespace GetStream\StreamLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class FeedManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'feed_manager';
    }
}
