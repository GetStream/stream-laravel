<?php namespace GetStream\StreamLaravel;

use GetStream\Stream\Client;

class StreamLaravelManager {

    public $client;
    private $config;

    public function __construct($api_key, $api_secret, $config)
    {
        $this->config = $config;
        if (getenv('STREAM_URL') !== false) {
            $this->client = Client::herokuConnect(getenv('STREAM_URL'));
        } else {
            $this->client = new Client($api_key, $api_secret);
            $location = $this->config->get("stream-laravel::location");
            $this->client->setLocation($location);
            $this->client->timeout = $this->config->get("stream-laravel::timeout", 3);
        }
        $this->userFeed = $this->config->get("stream-laravel::user_feed");
    }

    public function getUserFeed($user_id)
    {
        return $this->client->feed($this->userFeed, $user_id);
    }

    public function getNotificationFeed($user_id)
    {
        $user_feed = $this->config->get("stream-laravel::notification_feed");
        return $this->client->feed($user_feed, $user_id);
    }

    public function getNewsFeeds($user_id)
    {
        $feeds = array();
        $news_feeds = $this->config->get("stream-laravel::news_feeds");
        foreach ($news_feeds as $feed) {
            $feeds[$feed] = $this->client->feed($feed, $user_id);
        }
        return $feeds;
    }

    public function followUser($user_id, $target_user_id)
    {
        $news_feeds = $this->getNewsFeeds($user_id);
        $target_feed = $this->getUserFeed($target_user_id);
        foreach ($news_feeds as $feed) {
            $feed->followFeed($target_feed->getSlug(), $target_feed->getUserId());
        }
    }

    public function unfollowUser($user_id, $target_user_id)
    {
        $news_feeds = $this->getNewsFeeds($user_id);
        $target_feed = $this->getUserFeed($target_user_id);
        foreach ($news_feeds as $feed) {
            $feed->unfollowFeed($target_feed->getSlug(), $target_feed->getUserId());
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getFeed($feed, $user_id)
    {
        return $this->client->feed($feed, $user_id);
    }

    public function activityCreated($instance)
    {
        $activity = $instance->createActivity();
        $feed = $this->getFeed($this->userFeed, $instance->activityActorId());
        $feed->addActivity($activity);
    }

    public function activityDeleted($instance)
    {
        $foreignId = $instance->activityForeignId();
        $feed = $this->getFeed($this->userFeed, $instance->activityActorId());
        $feed->removeActivity($foreignId, true);
    }

}
