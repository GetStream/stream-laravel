<?php

use PHPUnit\Framework\TestCase;
use GetStream\Stream\Client;
use GetStream\StreamLaravel\Eloquent\Activity;

class _Activity extends Activity
{
    public $author = null;
    public $created_at = null;
    public $id = 42;

    public function __construct()
    {
        $this->client = new Client(null, null);
    }
    public function getKey()
    {
        return $this->id;
    }
    public function activityActorMethodName()
    {
        return 'author';
    }
    public function activityNotify()
    {
        return array($this->client->feed('feed', '1'));
    }
}

class ActivityTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->instance = new _Activity;
        $this->instance->author = new _Activity;
        $this->instance->author->id = 43;
        $this->instance->created_at = new \Datetime("now");
    }

    public function testCreateActivity()
    {
        $activity = $this->instance->createActivity();
        $this->assertSame($activity['verb'], '_activity');
        $this->assertSame($activity['actor'], '_Activity:43');
        $this->assertSame($activity['object'], '_Activity:42');
        $this->assertSame($activity['foreign_id'], '_Activity:42');
    }
    public function testToField()
    {
        $activity = $this->instance->createActivity();
        print_r($activity['to']);
        $this->assertSame($activity['to'], array('feed:1'));
    }
}
