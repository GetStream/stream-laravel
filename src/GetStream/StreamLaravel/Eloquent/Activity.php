<?php namespace GetStream\StreamLaravel\Eloquent;

use \Illuminate\Database\Eloquent\Model;

abstract class Activity extends Model {

    protected static $activitySyncPolicy = '\GetStream\StreamLaravel\Eloquent\CreateRemoveObserver';

    public function activityAuthorFeed()
    {
        return null;
    }

    public function activityLazyLoading()
    {
        return array();
    }

    public function activityExtraData()
    {
        return null;
    }

    public function activityActorMethodName()
    {
        return 'user';
    }

    public function activityActorId()
    {
        $actor = $this->{$this->activityActorMethodName()};
        return $actor->id;
    }

    public function activityActor()
    {
        $actor = $this->{$this->activityActorMethodName()};
        return Utils::createModelReference($actor);
    }

    public function activityVerb()
    {
        return strtolower(get_called_class());
    }

    public function activityObject()
    {
        return Utils::createModelReference($this);
    }

    public function activityForeignId()
    {
        return $this->activityObject();
    }

    public function activityTime()
    {
        return $this->created_at->format(\DateTime::ISO8601);
    }

    public function activityNotify()
    {
        return null;
    }

    public function createActivity()
    {
        $activity = array();
        $activity['actor'] = $this->activityActor();
        $activity['verb'] = $this->activityVerb();
        $activity['object'] = $this->activityObject();
        $activity['foreign_id'] = $this->activityForeignId();
        $activity['time'] = $this->activityTime();

        $to = $this->activityNotify();
        if ($to !== null){
            $activity['to'] = $to;
        }

        $extra_data = $this->activityExtraData();
        if ($extra_data !== null){
            $activity = array_merge($activity, $extra_data);
        }
        return $activity;
    }

    public static function boot()
    {
        parent::boot();
        static::observe(new static::$activitySyncPolicy);
    }

}
