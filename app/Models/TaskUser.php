<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskUser extends Pivot
{
    protected $connection = 'mysql';
}
