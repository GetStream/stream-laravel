<?php namespace GetStream\StreamLaravel\Eloquent;

class Utils {

    public static function createModelReference($instance)
    {
        $className = get_class($instance);
        return "$className:$instance->id";
    }

}
