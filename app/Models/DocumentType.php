<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'title', 'title_plural'];

    public function documents() {
        return $this->hasMany(Document::class);
    }
}
