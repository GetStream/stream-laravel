<?php

namespace GetStream\StreamLaravel\Eloquent;

use Illuminate\Database\Eloquent\Model;

abstract class Activity extends Model
{
    use ActivityTrait;
}
