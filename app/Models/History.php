<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

use App\Events\TaskModified;
use App\Events\DocumentModified;

class History extends Model
{
    protected $fillable = [
        'history_type_id', 'user_id'
    ];

    public static function boot() {
        parent::boot();

        static::created(function($item) {
            if ($item->task) {
                TaskModified::dispatch($item->task, $item->historyTypes->name, $item->historyTypes);

                // if ($item->historyTypes->name != 'close')
                $item->task->markAsUnread();
            }
            if ($item->document) {
                DocumentModified::dispatch($item->document, $item->historyTypes->name);

                // $item->document->checkApproveState();
            }
        });
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function document() {
        return $this->belongsTo(Document::class);
    }

    public function historyTypes() {
        return $this->belongsTo(HistoryType::class, 'history_type_id');
    }

    public function attachments() {
        return $this->hasMany(Attachment::class);   
    }
}
