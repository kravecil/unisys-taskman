<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\LazyCollection;

class Project extends Model
{
    protected $connection = 'mysql';

    protected $fillable = ['title'/* , 'description' */];

    // protected $with = ['tasks'];

    protected $appends = ['tasks_count'];

    public function user() {
    return $this->belongsTo(User::class);
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }

    public function tasksCount() : Attribute {
        // TEMP: исправить: выводить количество только доступных для пользователя поручений в проекте
        return new Attribute(get: function() {
            // return $this->tasks()->count();
            return auth()->user()?->tasks()->where('project_id', $this->id)->count();
        });
    }

    public static function index(Request $request) {
        if ($request->boolean('isMine', true)) {
            return $request->user()->projects()->latest()->simplePaginate(10)->items();
        } else {
            return LazyCollection::make(function () use ($request) {
                $projects = [];
                foreach($request->user()->tasksExecuting as $task)
                    if ($task->project != null )
                        if (!isset($projects[$task->project->id])) {
                            $projects[$task->project->id] = $task->project;
                            yield $task->project;
                        }
            })
            ->sortByDesc('created_at')
            ->slice(($request->input('page', 1) - 1) * 10, 10)
            ->values();
        }
        
    }

    public static function store(Request $request) {
        return $request->user()->projects()->create([
            'title' => $request->input('title'),
            // 'description' => $request->input('description'),
        ]);
    }
}
