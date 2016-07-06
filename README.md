##Stream Laravel
[![Build Status](https://travis-ci.org/GetStream/stream-laravel.svg?branch=master)](https://travis-ci.org/GetStream/stream-laravel) [![PHP version](https://badge.fury.io/ph/get-stream%2Fstream-laravel.svg)](http://badge.fury.io/ph/get-stream%2Fstream-laravel)

This package helps you create activity streams & newsfeeds with Laravel and [GetStream.io](https://getstream.io).

###Build activity streams & news feeds

![](https://dvqg2dogggmn6.cloudfront.net/images/mood-home.png)

You can build:

* Activity streams such as seen on Github
* A twitter style newsfeed
* A feed like instagram/ pinterest
* Facebook style newsfeeds
* A notification system

### Demo

You can check out our example app built using this library on Github [https://github.com/GetStream/Stream-Example-PHP/](https://github.com/GetStream/Stream-Example-PHP/)

###Table of Contents


###Installation

Begin by installing this package through Composer. Edit your project's composer.json file to require get-stream/stream-laravel.

```
"require": {
    "get-stream/stream-laravel": "~2.2.5"
},
```

Next, update Composer

```
composer update
```

Add ```'GetStream\StreamLaravel\StreamLaravelServiceProvider'``` to the list of providers in ```conf/app.php```

```
    'providers' => array(
        'GetStream\StreamLaravel\StreamLaravelServiceProvider',
        ...
    ),
```

Add FeedManager facade ```'GetStream\StreamLaravel\Facades\FeedManager'``` to list of aliases in ```conf/app.php```

```
    'aliases' => array(
        'FeedManager'       => 'GetStream\StreamLaravel\Facades\FeedManager',
        ...
    ),
```

Publish the configuration file

```
php artisan vendor:publish --provider="GetStream\StreamLaravel\StreamLaravelServiceProvider"
```

Login with Github on getstream.io and set ```api_key``` and ```api_secret``` in the stream-laravel config file as their are shown in your dashboard.

for example:
```php
return array(
    'api_key' => 'API_KEY',
    'api_secret' => 'API_SECRET',
    'api_app_id' => 'API_APP_ID',
    'location' => 'us-east',
    'timeout' => 3,
)
```

###Eloquent integration

Stream laravel instant integration with Eloquent models;extending the ```GetStream\StreamLaravel\Eloquent\Activity``` class will give you automatic tracking of your models to user feeds. 

For example:

```php

class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

```

Everytime a Pin is created it will be stored in the feed of the user that created it, and when a Pin instance is deleted than it will get removed as well.

####Activity fields

Models are stored in feeds as activities. An activity is composed of at least the following data fields: **actor**, **verb**, **object**, **time**. You can also add more custom data if needed.  

**object** is a reference to the model instance itself  
**actor** is a reference to the user attribute of the instance  
**verb** is a string representation of the class name

In order to work out-of-the-box the Activity class makes makes few assumptions:

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

####Activity extra data

Often you'll want to store more data than just the basic fields. You achieve this by implementing the ```activityExtraData``` method in the model.

NOTE: you should only return data that can be serialized by PHP's json_encode function

```php
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityExtraData()
    {
        return array('is_retweet'=>$this->is_retweet);
    }
```

####Customize activity verb

By default, the verb field is the class name of the activity, you can change that implementing the `activityVerb` method.

```php
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityVerb()
    {
        return 'pin';
    }

```

###Feed manager

Stream Laravel comes with a FeedManager class that helps with all common feed operations. You can get an instance of the manager with ```FeedManager``` if you defined the facade alias (see above in the install) or with ```App::make('feed_manager')``` if you did not.

####Feeds bundled with feed_manager

To get you started the manager has 4 feeds pre configured. You can add more feeds if your application needs it.
The three feeds are divided in three categories.

#####User feed:
The user feed stores all activities for a user. Think of it as your personal Facebook page. You can easily get this feed from the manager.  
```php
$feed = FeedManager::getUserFeed($user->id);
```  
#####News feeds:
The news feeds store the activities from the people you follow. 
There is both a timeline (similar to twitter) and an aggregated timeline (like facebook).

```php
$timelineFeed = FeedManager::getNewsFeed($user->id)['timeline'];
$aggregatedTimelineFeed = FeedManager::getNewsFeed($user->id)['timeline_aggregated'];
```
#####Notification feed:
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
            return array($targetFeed);
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
        return array($targetFeed);
    }
```


####Follow a feed
The create the newsfeeds you need to notify the system about follow relationships. The manager comes with APIs to let a user's news feeds follow another user's feed. This code lets the current user's timeline and timeline_aggregated feeds follow the target_user's personal feed.

```
FeedManager::followUser($userId, $targetId);
```

### Showing the newsfeed

####Activity enrichment

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
return View::make('feed', array('activities'=> $activities));
``` 



####Templating

Now that you've enriched the activities you can render them in a view.
For convenience we includes a basic view:

```
@section('content')
    <div class="container">
        <div class="container-pins">
            @foreach ($activities as $activity)
                @include('stream-laravel::render_activity', array('activity'=>$activity))
            @endforeach
        </div>
    </div>
@stop
```

The ```stream-laravel::render_activity``` view tag will render the view activity.$activity["verb"] view with the activity as context.

For example activity/tweet.blade.php will be used to render an normal activity with verb tweet and aggregated_activity/like.blade.php for an aggregated activity with verb like

If you need to support different kind of templates for the same activity, you can send a third parameter to change the view selection.  

The example below will use the view activity/homepage_like.html
```
@include('stream-laravel::render_activity', array('activity'=>$activity, 'prefix'=>'homepage'))
```


###Customizing enrichment

Sometimes you'll want to customize how enrichment works. The documentation will show you several common options.

####Enrich extra fields

If you store references to model instances in the activity extra_data you can use the Enrich class to take care of it for you

```
use \Illuminate\Database\Eloquent\Model;
use GetStream\StreamLaravel\Enrich;

class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityExtraData()
    {
        $ref = Utils::createModelReference($this->parentTweet);
        return array('parent_tweet' => $ref);
    }

// tell the enricher to enrich parent_tweet
$enricher = new Enrich(array('actor', 'object', 'parent_tweet'));
$activities = $feed->getActivities(0,25)['results'];
$activities = $enricher->enrichActivities($activities);
```

####Preload related data

You will commonly access related objects such as activity['object']->user. To prevent your newsfeed to run N queries you can instruct the manager to load related objects. The manager will use Eloquent's ```With``` functionality.

```
class Pin extends Eloquent {
    use GetStream\StreamLaravel\Eloquent\ActivityTrait;

    public function activityLazyLoading()
    {
        return array('user');
    }
```

###Low level APIs access
When needed you can also use the low level PHP client API directly.
The full explanation can be found in the [getstream.io documentation](https://getstream.io/docs/).


```
$specialFeed = FeedManager::getClient->feed('special', '42')
$specialFeed->followFeed('timeline', '60')
```
