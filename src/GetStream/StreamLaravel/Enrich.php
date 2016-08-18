<?php namespace GetStream\StreamLaravel;

use GetStream\StreamLaravel\EnrichedActivity;
use GetStream\StreamLaravel\Exceptions\MissingDataException;

class Enrich {

    private $fields = array();

    public function __construct($fields = array('actor', 'object'), $withTrashed = true)
    {
        $this->fields = $fields;
        $this->withTrashed = $withTrashed;
    }

    public function fromDb($model, $ids, $with=array())
    {
        $results = array();
        $pkName = (new $model())->getKeyName();
        $query = $model::with($with)->whereIn($pkName, $ids);
        if (in_array('Illuminate\Database\Eloquent\SoftDeletingTrait', class_uses(get_class($model))) && $this->withTrashed) // previous withTrash method deprecated in Laravel 4.2
            $query = $query->withTrashed();
        $objects = $query->get();
        foreach ($objects as $object) {
            $results[$object->getKey()] = $object; // support for non-default UUID keys
        }
        return $results;
    }

    private function collectReferences($activities)
    {
        $model_references = array();
        foreach ($activities as $key => $activity) {
            foreach ($activity as $field=>$value) {
                if ($value === null) {
                    continue;
                }
                if (in_array($field, $this->fields)) {
                    $reference = explode(':', $value);
                    $model_references[$reference[0]][$reference[1]] = 1;
                }
            }
        }
        return $model_references;
    }

    private function retrieveObjects($references)
    {
        $objects = array();
        foreach ($references as $content_type => $content_ids) {
            $content_type_model = new $content_type;
            $with = array();
            if (property_exists($content_type_model, 'activityLazyLoading')) {
                $with = $content_type_model->activityLazyLoading;
            }
            $fetched = $this->fromDb($content_type_model, array_keys($content_ids), $with);
            $objects[$content_type] = $fetched;
        }
        return $objects;
    }

    private function wrapActivities($activities)
    {
        $wrappedActivities = array();
        foreach ($activities as $i => $activity) {
            $wrappedActivities[] = new EnrichedActivity($activity);
        }
        return $wrappedActivities;
    }

    private function injectObjects(&$activities, $objects)
    {
        foreach ($activities as $key => $activity) {
            foreach ($this->fields as $field) {
                if (!isset($activity[$field]))
                    continue;
                $value = $activity[$field];
                $reference = explode(':', $value);
                if (!array_key_exists($reference[0], $objects)) {
                    $activity->trackNotEnrichedField($reference[0], $reference[1]);
                    continue;
                }
                if (!array_key_exists($reference[1], $objects[$reference[0]])) {
                    $activity->trackNotEnrichedField($reference[0], $reference[1]);
                    continue;
                }
                $activities[$key][$field] = $objects[$reference[0]][$reference[1]];
            }
        }
        return $activities;
    }

    private function injectCarbonTimestamps($activities, $timestampKey)
    {
        foreach ($activities as $key => $activity) {
            if ($activity->offsetExists($timestampKey)) {
                $activities[$key][$timestampKey] = new \Carbon\Carbon($activity[$timestampKey]);
            }
        }

        return $activities;
    }

    public function enrichActivities($activities)
    {
        $activities = $this->wrapActivities($activities);

        if (count($activities) === 0) {
            return $activities;
        }

        $references = $this->collectReferences($activities);
        $objects = $this->retrieveObjects($references);
        $activities = $this->injectObjects($activities, $objects);
        $activities = $this->injectCarbonTimestamps($activities, 'time');

        return $activities;
    }

    public function enrichAggregatedActivities($aggregatedActivities)
    {
        foreach ($aggregatedActivities as $i => $aggregated) {
            $aggregatedActivities[$i]['activities'] = $this->wrapActivities($aggregated['activities']);
        }

        if (count($aggregatedActivities) === 0) {
            return $aggregatedActivities;
        }

        $references = array();
        foreach ($aggregatedActivities as $aggregated) {
            $references = array_replace_recursive($references, $this->collectReferences($aggregated['activities']));
        }

        $aggregatedActivities = $this->injectCarbonTimestamps($aggregatedActivities, 'updated_at');

        $objects = $this->retrieveObjects($references);
        foreach ($aggregatedActivities as $key => $aggregated) {
            $this->injectObjects($aggregatedActivities[$key]['activities'], $objects);
        }
        return $aggregatedActivities;
    }

}
