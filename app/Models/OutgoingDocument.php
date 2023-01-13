<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingDocument extends Model
{
    protected $fillable = [
        'number',
        'registrar_id',
        'signer_id',
        'executor_id',
        'receiver',
        'description',
    ];

    // protected $casts = ['created_at' => 'datetime:d.m.Y H:i:s'];

    protected $with = ['signer', 'executor'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }

    public function signer() {
        return $this->belongsTo(User::class, 'signer_id');
    }

    public function executor() {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public static function getLastNumber() {
        return OutgoingDocument::whereYear('created_at', now()->year)->orderByDesc('number')->first()?->number + 1 ?? 1;
    }
}
