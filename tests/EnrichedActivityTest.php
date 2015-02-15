<?php

use GetStream\StreamLaravel\EnrichedActivity;
use Mockery as m;


class EnrichedActivityTest extends \PHPUnit_Framework_TestCase
{

    public function testArrayImplementation()
    {
        $activity = new EnrichedActivity(array('actor' => 'stream'));
        $this->assertSame($activity['actor'], 'stream');
        $activity['x'] = 1;
        $this->assertSame($activity['x'], 1);
    }

    public function testTrackNotEnrichedData()
    {
        $activity = new EnrichedActivity(array());
        $this->assertSame($activity->getNotEnrichedData(), array());
    }

    public function testTrackNotEnrichedField()
    {
        $activity = new EnrichedActivity(array());
        $this->assertTrue($activity->enriched());
        $activity->trackNotEnrichedField('missing', 'value');
        $this->assertFalse($activity->enriched());
        $this->assertSame($activity->getNotEnrichedData(), array('missing' => 'value'));
    }

    public function testIterable()
    {
        $activity = new EnrichedActivity(array('1'=>1, '2'=> 3));
        $sum = 0;
        foreach ($activity as $field => $value) {
            $sum += $value;
        }
        $this->assertSame($sum, 4);
    }

}
