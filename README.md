# Stream Laravel
[![Build Status](https://travis-ci.org/GetStream/stream-laravel.svg?branch=master)](https://travis-ci.org/GetStream/stream-laravel) [![PHP version](https://badge.fury.io/ph/get-stream%2Fstream-laravel.svg)](http://badge.fury.io/ph/get-stream%2Fstream-laravel)

[stream-laravel](https://github.com/GetStream/stream-laravel) is a Laravel client for [Stream](https://getstream.io/). You can use this in any Laravel application, or in any application that uses Eloquent ORM ([`illuminate/database`](https://packagist.org/packages/illuminate/database)) as a standalone ORM.

You can sign up for a Stream account at https://getstream.io/get_started.

Note there is also a lower level [PHP - Stream integration](https://github.com/getstream/stream-php) library which is suitable for all PHP applications.

## Build Activity Streams, News Feeds, and More

![](https://dvqg2dogggmn6.cloudfront.net/images/mood-home.png)

You can build:

* Activity Streams - like the one seen on GitHub
* A Twitter-like feed
* Instagram / Pinterest Photo Feeds
* Facebook-style newsfeeds
* A Notification System
* Lots more...

## Demos

https://github.com/GetStream/Stream-Laravel-Example

https://github.com/GetStream/Stream-Example-PHP


## Installation

### Composer

***Begin by installing this package through Composer. Edit your project's ```composer.json``` file to require ```get-stream/stream-laravel```:***

```
"require": {
    "get-stream/stream-laravel": "~2.3.5"
},
```

***Next, update Composer:***

```
composer update
```

### Laravel

**Laravel prior to 5.5** (no longer supported)

Add `'GetStream\StreamLaravel\StreamLaravelServiceProvider'` to your list of providers in `config/app.php`:

```
'providers' => [
    GetStream\StreamLaravel\StreamLaravelServiceProvider::class,
    ...
],
```

And add the `FeedManager` facade `'GetStream\StreamLaravel\Facades\FeedManager'` to your list of aliases in `config/app.php`:

```
'aliases' => [
    'FeedManager' => GetStream\StreamLaravel\Facades\FeedManager::class,
    ...
],
```

***Publish the configuration file:***

```
php artisan vendor:publish --provider="GetStream\StreamLaravel\StreamLaravelServiceProvider"
```

This will create ```config/stream-laravel.php```. We will set our credentials after they are created in the Stream Dashboard.

### GetStream.io Dashboard

***Now, login to [GetStream.io](https://getstream.io) and create an application in the dashboard.***

***Retrieve the API key, API secret, and API app id, which are shown in your dashboard.***

***Create feeds in your new application. By default, you should create the following:***

- *user* which is a _flat_ feed.
- *timeline* which is a _flat_ feed.
- *timeline_aggregated* which is an _aggregated_ feed.
- *notification* which is a _notification_ feed.


### Stream-Laravel Config File

***Set your key, secret, and app id in ```config/stream-laravel.php``` file as their are shown in your dashboard. Also set the location for good measure. For example:***

```
return [

    /*
    |-----------------------------------------------------------------------------
    | Your GetStream.io API credentials (you can them from getstream.io/dashboard)
    |-----------------------------------------------------------------------------
    |
    */

    'api_key' => '[API KEY HERE]',
    'api_secret' => '[API SECRET HERE]',
    'api_app_id' => '[API APP ID HERE]',
    /*
    |-----------------------------------------------------------------------------
    | Client connection options
    |-----------------------------------------------------------------------------
    |
    */
    'location' => 'us-east',
    'timeout' => 3,
    /*
    |-----------------------------------------------------------------------------
    | The default feed manager class
    |-----------------------------------------------------------------------------
    |
    */

```

***You can also set the name of your feeds here:***

```
/*
    |-----------------------------------------------------------------------------
    | The feed that keeps content created by its author
    |-----------------------------------------------------------------------------
    |
    */
    'user_feed' => 'user',
    /*
    |-----------------------------------------------------------------------------
    | The feed containing notification activities
    |-----------------------------------------------------------------------------
    |
    */
    'notification_feed' => 'notification',
    /*
    |-----------------------------------------------------------------------------
    | The feeds that shows activities from followed user feeds
    |-----------------------------------------------------------------------------
    |
    */
    'news_feeds' => [
        'timeline' => 'timeline',
        'timeline_aggregated' => 'timeline_aggregated',
    ]
```

And that should get you off and running with Stream-Laravel. Have lots of fun!

### Lumen Installation

Begin by installing this package through Composer.

```
composer require get-stream/stream-laravel
```

Add `'GetStream\StreamLaravel\StreamLumenServiceProvider'` to the list of providers in `bootstrap/app.php`

```php
$app->register(\GetStream\StreamLaravel\StreamLumenServiceProvider::class);
```

Manually create a config file in ./config/stream-laravel.php...

```php
<?php

return [
    'api_key' => 'API_KEY',
    'api_secret' => 'API_SECRET',
    'api_app_id' => 'API_APP_ID',
    'location' => 'us-east',
    'timeout' => 3,
];
```

and tell Lumen to configure it, in bootstrap.

```php
$app->configure('stream-laravel');
```

# Features of Stream-Laravel

## Eloquent Integration

Stream-Laravel provides instant integration with Eloquent models - extending the ```GetStream\StreamLaravel\Eloquent\Activity``` class will give you automatic tracking of your models to user feeds.

For example:

```php

class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

```

Everytime a Pin is created it will be stored in the feed of the user that created it, and when a Pin instance is deleted than it will get removed as well.

Automatically!

### Activity Fields

Models are stored in feeds as activities. An activity is composed of at least the following data fields: **actor**, **verb**, **object**, **time**. You can also add more custom data if needed.

**object** is a reference to the model instance itself
**actor** is a reference to the user attribute of the instance
**verb** is a string representation of the class name

In order to work out-of-the-box the Activity class makes few assumptions:

1. the Model class belongs to a user
2. the model table has timestamp columns (created_at is required)

You can change how a model instance is stored as activity by implementing specific methods as explained later.

Below shows an example how to change your class if the model belongs to an author instead of to a user.

```php
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function author()
    {
        return $this->belongsTo('Author');
    }

    public function activityActorMethodName()
    {
        return 'author';
    }
```

### Activity Extra Data

Often, you'll want to store more data than just the basic fields. You achieve this by implementing the ```activityExtraData``` method in the model.

NOTE: you should only return data that can be serialized by PHP's json_encode function

```php
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityExtraData()
    {
        return ['is_retweet' => $this->is_retweet];
    }
```

### Customize Activity Verb

By default, the verb field is the class name of the activity, you can change that implementing the `activityVerb` method.

```php
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityVerb()
    {
        return 'pin';
    }

```

## Feed Manager

Stream Laravel comes with a FeedManager class that helps with all common feed operations. You can get an instance of the manager with ```FeedManager``` if you defined the facade alias (see above in the install), or with ```App::make('feed_manager')``` if you did not.

## Pre-Bundled Feeds

To get you started the manager has feeds pre configured. You can add more feeds if your application needs it. The three feeds are divided in three categories.

### User Feed:
The user feed stores all activities for a user. Think of it as your personal Facebook page. You can easily get this feed from the manager.
```php
$feed = FeedManager::getUserFeed($user->id);
```
### News Feed:
The news feeds store the activities from the people you follow.
There is both a timeline (similar to twitter) and an aggregated timeline (like facebook).

```php
$timelineFeed = FeedManager::getNewsFeeds($user->id)['timeline'];
$aggregatedTimelineFeed = FeedManager::getNewsFeeds($user->id)['timeline_aggregated'];
```
### Notification Feed:
The notification feed can be used to build notification functionality.

![Notification feed](http://feedly.readthedocs.org/en/latest/_images/fb_notification_system.png)

Below we show an example of how you can read the notification feed.
```php
notification_feed = FeedManager::getNotificationFeed($user->id);

```
By default the notification feed will be empty. You can specify which users to notify when your model gets created. In the case of a retweet you probably want to notify the user of the parent tweet.

```php
class Tweet extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityNotify()
    {
        if ($this->isRetweet) {
            $targetFeed = FeedManager::getNotificationFeed($this->parent->user->id);
            return [$targetFeed];
        }
    }
```

Another example would be following a user. You would commonly want to notify the user which is being followed.

```php
class Follow extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function target()
    {
        return $this->belongsTo('User');
    }

    public function activityNotify()
    {
        $targetFeed = FeedManager::getNotificationFeed($this->target->id);
        return [$targetFeed];
    }
```


## Follow Feed
To create the newsfeeds you need to notify the system about follow relationships. The manager comes with APIs to let a user's news feeds follow another user's feed. This code lets the current user's timeline and timeline_aggregated feeds follow the target_user's personal feed.

```
FeedManager::followUser($userId, $targetId);
```

## Displaying the Newsfeed

### Activity Enrichment

When you read data from feeds, a like activity will look like this:

```
{'actor': 'User:1', 'verb': 'like', 'object': 'Like:42'}
```

This is far from ready for usage in your template. We call the process of loading the references from the database enrichment. An example is shown below:

```
use GetStream\StreamLaravel\Enrich;

$enricher = new Enrich();
$feed = FeedManager::getNewsFeeds(Auth::id())['timeline'];
$activities = $feed->getActivities(0,25)['results'];
$activities = $enricher->enrichActivities($activities);
return View::make('feed', ['activities' => $activities]);
```

The enrich method returns an array of objects of type ```EnrichedActivity``` which you can also parse yourself.  For example, in an API where you are using ```spatie/laravel-fractal``` you could use a loop like the following in your Controller to return json to your api.

On your model:
```php
use App\Transformers\MyModelEnrichTransformer;
use GetStream\StreamLaravel\Eloquent\ActivityTrait;
use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
    public function enrichTransformer() {
        return new MyModelEnrichTransformer();
    }
}
```

In your controller:
```php
use GetStream\StreamLaravel\Enrich;
$feed = FeedManager::getNewsFeeds($user->id)['timeline'];
$enricher = new Enrich();
$activities = $feed->getActivities(0, 25)['results'];
$activities = $enricher->enrichActivities($activities);

$collection = new Collection();
foreach ($activities as $activity) {
    $record = [
        "actor" => $this->transformData($activity["actor"], $activity["actor"]->enrichTransformer()),
        "object" => $this->transformData($activity["object"], $activity["object"]->enrichTransformer()),
        "verb" => $activity["verb"],
        "foreign_id" => $activity["foreign_id"],
        "time" => $activity["time"],
    ];

    if (!empty($activity["target"])) {
        array_push($record, [
            "target" => $this->transformData($activity["target"], $activity["target"]->enrichTransformer()),
        ]);
    }

    $collection->push($record);
}

return response()->json($collection);

```


### Templating

Now that you've enriched the activities you can render them in a view.
For convenience we includes a basic view:

```
@section('content')
    <div class="container">
        <div class="container-pins">
            @foreach ($activities as $activity)
                @include('stream-laravel.render_activity', ['activity' => $activity])
            @endforeach
        </div>
    </div>
@stop
```

The ```stream-laravel.render_activity``` view tag will render the view activity.$activity["verb"] view with the activity as context.

For example activity/tweet.blade.php will be used to render an normal activity with verb tweet and aggregated_activity/like.blade.php for an aggregated activity with verb like

If you need to support different kind of templates for the same activity, you can send a third parameter to change the view selection.

The example below will use the view activity/homepage_like.html
```
@include('stream-laravel.render_activity', ['activity' => $activity, 'prefix' => 'homepage'])
```


### Customizing Enrichment

Sometimes you'll want to customize how enrichment works. The documentation will show you several common options.

### Enrich Extra Fields

If you store references to model instances in the activity extra_data you can use the Enrich class to take care of it for you:

```
use \Illuminate\Database\Eloquent\Model;
use GetStream\StreamLaravel\Enrich;

class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityExtraData()
    {
        $ref = Utils::createModelReference($this->parentTweet);
        return ['parent_tweet' => $ref];
    }

// tell the enricher to enrich parent_tweet
$enricher = new Enrich(['actor', 'object', 'parent_tweet']);
$activities = $feed->getActivities(0,25)['results'];
$activities = $enricher->enrichActivities($activities);
```

### Preload Related Data

You will commonly access related objects such as activity['object']->user. To prevent your newsfeed to run N queries you can instruct the manager to load related objects. The manager will use Eloquent's ```With``` functionality.

```
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityLazyLoading()
    {
        return ['user'];
    }
```

### Full documentation and Low level APIs access

When needed you can also use the [low level PHP API](https://github.com/getstream/stream-php) directly. Documentation is available at the [Stream website](https://getstream.io/docs/?language=php).

```
$specialFeed = FeedManager::getClient->feed('special', '42')
$specialFeed->followFeed('timeline', '60')
```

### Copyright and License Information

Copyright (c) 2014-2019 Stream.io Inc, and individual contributors. All rights reserved.

See the file "LICENSE" for information on the history of this software, terms & conditions for usage, and a DISCLAIMER OF ALL WARRANTIES.
