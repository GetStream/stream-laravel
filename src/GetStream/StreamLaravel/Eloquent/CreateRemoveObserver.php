<?php

namespace GetStream\StreamLaravel\Eloquent;

use Illuminate\Support\Facades\App;

class CreateRemoveObserver
{
    public function created($model)
    {
        $manager = App::make('feed_manager');
        $manager->activityCreated($model);
    }

    public function deleting($model)
    {
        $manager = App::make('feed_manager');
        $manager->activityDeleted($model);
    }
}
