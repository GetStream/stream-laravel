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
        $config->shouldReceive('get')->once()->with('stream-laravel::location')
            ->andReturn('');
        $config->shouldReceive('get')->once()->with('stream-laravel::news_feeds')
            ->andReturn(array('flat'=>'flat', 'aggregated'=>'aggregated'));
        $config->shouldReceive('get')->once()->with('stream-laravel::timeout', 3)
            ->andReturn(3);
        $this->manager = new StreamLaravelManager('key', 'secret', $config);
    }

    public function testDefaultTimeout(){
        $client = $this->manager->getClient();
        $this->assertSame($client->timeout, 3);
    }

    public function testCustomTimeout(){
        parent::setUp();
        $config = m::mock('ConfigMock');
        $config->shouldReceive('get')->once()->with('stream-laravel::user_feed')
            ->andReturn('user');
        $config->shouldReceive('get')->once()->with('stream-laravel::notification_feed')
            ->andReturn('notification');
        $config->shouldReceive('get')->once()->with('stream-laravel::location')
            ->andReturn('');
        $config->shouldReceive('get')->once()->with('stream-laravel::news_feeds')
            ->andReturn(array('flat'=>'flat', 'aggregated'=>'aggregated'));
        $config->shouldReceive('get')->once()->with('stream-laravel::timeout', 3)
            ->andReturn(6);
        $manager = new StreamLaravelManager('key', 'secret', $config);

        $client = $manager->getClient();
        $this->assertSame($client->timeout, 6);
    }


    public function testGetUserFeed(){
        $feed = $this->manager->getUserFeed(42);
        $this->assertSame($feed->getId(), 'user:42');
    }

    public function testGetNotificationFeed()
    {
        $feed = $this->manager->getNotificationFeed(42);
        $this->assertSame($feed->getId(), 'notification:42');
    }

    public function testGetNewsFeeds()
    {
        $feeds = $this->manager->getNewsFeeds(42);
        $this->assertSame($feeds['aggregated']->getId(), 'aggregated:42');
        $this->assertSame($feeds['flat']->getId(), 'flat:42');
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
        $this->assertSame($feed->getId(), 'myfeed:42');
    }

    public function testActivityCreated()
    {
    }

    public function testActivityDeleted()
    {
    }

}
