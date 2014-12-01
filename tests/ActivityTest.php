<?php

use GetStream\StreamLaravel\StreamLaravelManager;
use GetStream\StreamLaravel\Eloquent\Activity;
use GetStream\Stream\Feed;
use Mockery as m;

class _Activity extends Activity
{
    public $author = null;
    public $created_at = null;
    public $id = 42;
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
        return array(new Feed(null, 'feed', '1', 'token', null));
    }
}

class ActivityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
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
