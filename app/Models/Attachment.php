<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['name', 'path'];

    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function comment() {
        return $this->belongsTo(History::class);
    }

    public function document() {
        return $this->belongsTo(Document::class);
    }
}
