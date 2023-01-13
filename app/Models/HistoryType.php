<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryType extends Model
{
    public $timestamps = false;

    public function tasks() {
        return $this->hasMany(Task::class, 'state');
    }
    public function histories() {
        return $this->hasMany(History::class);
    }
}
