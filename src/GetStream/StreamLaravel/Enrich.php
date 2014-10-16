<?php namespace GetStream\StreamLaravel;

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
        $query = $model::with($with)->whereIn('id', $ids);
        if (property_exists($model, 'withTrashed') && $this->withTrashed)
            $query = $query->withTrashed();
        $objects = $query->get();
        foreach ($objects as $object) {
            $results[$object->id] = $object;
        }
        return $results;
    }

    private function collectReferences($activities)
    {
        $model_references = array();
        foreach ($activities as $key => $activity) {
            foreach ($activity as $field=>$value) {
                if (in_array($field, $this->fields)){
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
            if (count($fetched) < count(array_keys($content_ids))) {
                $missing_ids = array_values(array_diff(array_keys($content_ids), array_keys($fetched)));
                $pretty_ids = var_export($missing_ids, true);
                throw new MissingDataException("Some data in this feed is not in the database: model: {$content_type} ids:{$pretty_ids}");
            }
            $objects[$content_type] = $fetched;
        }
        return $objects;
    }

    private function injectObjects(&$activities, $objects)
    {
        foreach ($activities as $key => $activity) {
            foreach ($this->fields as $field) {
                if (!array_key_exists($field, $activity))
                    continue;
                $value = $activity[$field];
                $reference = explode(':', $value);
                if (!array_key_exists($reference[0], $objects))
                    continue;
                if (!array_key_exists($reference[1], $objects[$reference[0]]))
                    continue;
                $activities[$key][$field] = $objects[$reference[0]][$reference[1]];
            }
        }
        return $activities;
    }

    public function enrichActivities($activities)
    {
        if (count($activities) === 0) {
            return $activities;
        }

        $references = $this->collectReferences($activities);
        $objects = $this->retrieveObjects($references);
        $activities = $this->injectObjects($activities, $objects);
        return $activities;
    }

    public function enrichAggregatedActivities($aggregatedActivities)
    {
        if (count($aggregatedActivities) === 0) {
            return $aggregatedActivities;
        }

        $references = array();
        foreach ($aggregatedActivities as $aggregated) {
            $references = array_replace_recursive($references, $this->collectReferences($aggregated['activities']));
        }

        $objects = $this->retrieveObjects($references);
        foreach ($aggregatedActivities as $key => $aggregated) {
            $aggregatedActivities[$key]['updated_at'] = new \Carbon\Carbon($aggregatedActivities[$key]['updated_at']);
            $this->injectObjects($aggregatedActivities[$key]['activities'], $objects);
        }
        return $aggregatedActivities;
    }

}
