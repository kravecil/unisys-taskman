<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Partner extends Model
{
    protected $fillable = ['name', 'inn', 'address'];

    public $timestamps = false;
}
