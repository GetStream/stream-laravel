<?php

use PHPUnit\Framework\TestCase;
use GetStream\StreamLaravel\EnrichedActivity;

class EnrichedActivityTest extends TestCase
{
    public function testArrayImplementation()
    {
        $activity = new EnrichedActivity(['actor' => 'stream']);
        $this->assertSame($activity['actor'], 'stream');
        $activity['x'] = 1;
        $this->assertSame($activity['x'], 1);
    }

    public function testTrackNotEnrichedData()
    {
        $activity = new EnrichedActivity([]);
        $this->assertSame($activity->getNotEnrichedData(), []);
    }

    public function testTrackNotEnrichedField()
    {
        $activity = new EnrichedActivity([]);
        $this->assertTrue($activity->enriched());
        $activity->trackNotEnrichedField('missing', 'value');
        $this->assertFalse($activity->enriched());
        $this->assertSame($activity->getNotEnrichedData(), ['missing' => 'value']);
    }

    public function testIterable()
    {
        $activity = new EnrichedActivity(['1' => 1, '2' => 3]);
        $sum = 0;
        foreach ($activity as $field => $value) {
            $sum += $value;
        }
        $this->assertSame($sum, 4);
    }
}
