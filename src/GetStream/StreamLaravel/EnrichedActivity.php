<?php namespace GetStream\StreamLaravel;

class EnrichedActivity implements \ArrayAccess, \Iterator {
    private $activityData = array();
    private $notEnrichedData = array();

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

    // Array iteration methods
    public function rewind()
    {
        reset($this->activityData);
    }
  
    public function current()
    {
        return current($this->activityData);
    }
  
    public function key() 
    {
        return key($this->activityData);
    }
  
    public function next() 
    {
        return next($this->activityData);
    }
  
    public function valid()
    {
        return (bool) $this->current();
    }

}
