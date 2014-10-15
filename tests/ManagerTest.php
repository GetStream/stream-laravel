<?php

use GetStream\StreamLaravel\StreamLaravelManager;
use Mockery as m;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp(){
        parent::setUp();
        $config = m::mock('ConfigMock');
        $config->shouldReceive('get')->once()->with('stream-laravel::user_feed')
            ->andReturn('user');
        $config->shouldReceive('get')->once()->with('stream-laravel::notification_feed')
            ->andReturn('notification');
        $config->shouldReceive('get')->once()->with('stream-laravel::news_feeds')
            ->andReturn(array('flat'=>'flat', 'aggregated'=>'aggregated'));
        $this->manager = new StreamLaravelManager('key', 'secret', $config);
    }

    public function testGetUserFeed(){
        $feed = $this->manager->getUserFeed(42);
        $this->assertSame($feed->getFeedId(), 'user:42');
    }

    public function testGetNotificationFeed()
    {
        $feed = $this->manager->getNotificationFeed(42);
        $this->assertSame($feed->getFeedId(), 'notification:42');
    }

    public function testGetNewsFeeds()
    {
        $feeds = $this->manager->getNewsFeeds(42);
        $this->assertSame($feeds['aggregated']->getFeedId(), 'aggregated:42');
        $this->assertSame($feeds['flat']->getFeedId(), 'flat:42');
    }

    public function testFollowUser()
    {
    }

    public function testUnfollowUser()
    {
    }

    public function testGetFeed()
    {
        $feed = $this->manager->getFeed('myfeed', 42);
        $this->assertSame($feed->getFeedId(), 'myfeed:42');
    }

    public function testActivityCreated()
    {
    }

    public function testActivityDeleted()
    {
    }

}
