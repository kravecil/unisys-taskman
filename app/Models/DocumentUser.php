<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DocumentUser extends Pivot
{
    protected $connection = 'mysql';
}
