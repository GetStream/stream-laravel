<?php

namespace GetStream\StreamLaravel\Eloquent;

use GetStream\StreamLaravel\Exceptions\ModelReferenceException;

class Utils
{
    public static function createModelReference($instance)
    {
        $className = get_class($instance);
        $pk = $instance->getKey();

        if (empty($pk)) {
            throw new ModelReferenceException("Could not create a reference for instance of class $className", 1);
        }

        return "$className:$pk";
    }
}
