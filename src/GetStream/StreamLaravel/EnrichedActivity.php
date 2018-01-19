<?php

namespace GetStream\StreamLaravel;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

class EnrichedActivity implements ArrayAccess, IteratorAggregate
{
    private $activityData = [];
    private $notEnrichedData = [];

    public function __construct($activityData)
    {
        $this->activityData = $activityData;
    }

    public function trackNotEnrichedField($field, $value)
    {
        $this->notEnrichedData[$field] = $value;
    }

    public function getNotEnrichedData()
    {
        return $this->notEnrichedData;
    }

    public function enriched()
    {
        return (count($this->notEnrichedData) == 0);
    }

    // Array implementation methods
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->activityData[] = $value;
        } else {
            $this->activityData[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->activityData[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->activityData[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->activityData[$offset]) ? $this->activityData[$offset] : null;
    }

    // Support iteration over private activityData array
    public function getIterator()
    {
        return new ArrayIterator($this->activityData);
    }
}
