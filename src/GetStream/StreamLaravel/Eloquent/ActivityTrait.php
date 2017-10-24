<?php

namespace GetStream\StreamLaravel\Eloquent;

trait ActivityTrait
{
    /**
     * @var string
     */
    protected static $activitySyncPolicy = '\GetStream\StreamLaravel\Eloquent\CreateRemoveObserver';

    /**
     *  Boot Observer
     */
    public static function bootActivityTrait()
    {
        static::observe(new static::$activitySyncPolicy);
    }

    /**
     * Returns an array of relations as strings or functions that tell the enrich
     * class which (and how) related models should be loaded. By default the result
     * of this is passed directly to Eloquent's query With lazy loader method
     * @return array
     */
    public function activityLazyLoading()
    {
        return [];
    }

    /**
     * The extra data that should be part of the activity. The extra data will be serialized
     * using PHP's built-in json_encode function
     * @return array[string]
     */
    public function activityExtraData()
    {
        return null;
    }

    /**
     * The name of the property that holds a reference to the author/owner of the model instance
     * @return string
     */
    public function activityActorMethodName()
    {
        return 'user';
    }

    /**
     * The id of the author/owner of the model instance
     * @return int|string
     */
    public function activityActorId()
    {
        $actor = $this->{$this->activityActorMethodName()};

        return $actor->id;
    }

    /**
     * The reference to the model instance of the author/owner
     *
     * @return string
     */
    public function activityActor()
    {
        $actor = $this->{$this->activityActorMethodName()};

        return Utils::createModelReference($actor);
    }

    /**
     * The activity verb for this instance
     * @return string
     */
    public function activityVerb()
    {
        return strtolower(get_called_class());
    }

    /**
     * The activity object for this instance
     *
     * @return string
     */
    public function activityObject()
    {
        return Utils::createModelReference($this);
    }

    /**
     * The activity foreign_id for this instance
     *
     * @return string
     */
    public function activityForeignId()
    {
        return $this->activityObject();
    }

    /**
     * The activity time for this instance
     *
     * @return string
     */
    public function activityTime()
    {
        return $this->created_at->format(\DateTime::ISO8601);
    }

    /**
     * The feeds that should receive a copy of this instance when it's created
     *
     * @return \GetStream\Stream\Feed[]
     */
    public function activityNotify()
    {
        return null;
    }

    /**
     * The activity data for this instance
     *
     * @return array
     */
    public function createActivity()
    {
        $activity = [];
        $activity['actor'] = $this->activityActor();
        $activity['verb'] = $this->activityVerb();
        $activity['object'] = $this->activityObject();
        $activity['foreign_id'] = $this->activityForeignId();
        $activity['time'] = $this->activityTime();

        $to = $this->activityNotify();

        if ( $to !== null )
        {
            $activity['to'] = [];
            foreach ( $to as $feed )
            {
                $activity['to'][] = $feed->getId();
            }
        }

        $extra_data = $this->activityExtraData();

        if ( $extra_data !== null )
        {
            $activity = array_merge($activity, $extra_data);
        }

        return $activity;
    }
}
