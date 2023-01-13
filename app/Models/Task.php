<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DateTime, DateTimeInterface;

use App\Events\TaskModified;

class Task extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'body', 'deadline_at', 'closed_at', 'is_note', 'is_coexecutors', 'state', 'request_deadline_at',
        'project_id', 'importance',
        'repeat_in',
    ];

    protected $dates = [
        'deadline_at',
        'closed_at',
        'request_deadline_at',
    ];

    protected $with = [
      'creators',
      'executors',
      'coexecutors',
      'controllers',
      'state',
      'attachments',
      'project',
      // 'task',
      // 'tasks', //?
      // 'document',
    ];

    protected $appends = [
        'deadline_is_close',
        'days_till_deadline',
        'is_unread',
        'user_child_tasks_count',
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($item) {
            $item->attachments()->each(function($attachment) {
                Storage::delete($attachment->path);
            });

            $item->comments()->each(function($comment) {
                $comment->attachments()->each(function($attachment) {
                    Storage::delete($attachment->path);
                });
            });
        });
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i');
    }

    public function task() {
      return $this->belongsTo(Task::class);
    }
    
    /**
     * Выводит все подзадачи
     */
    public function tasks() {
      return $this->hasMany(Task::class);
    }

    /**
     * Выводит все подзадачи без заметок
     */
    public function tasksWithoutNotes() {
      return $this->hasMany(Task::class)->where('is_note', false);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function attachments() {
        return $this->hasMany(Attachment::class);
    }

    public function histories() {
        return $this->hasMany(History::class);
    }

    public function document() {
        return $this->belongsTo(Document::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function users() {
        return $this
            ->belongsToMany(User::class, config('database.connections.mysql.database').'.task_user', 'task_id', 'user_id')
            ->using(TaskUser::class)
            ->withPivot('read_at');
    }

    public function relatedUsers($userTypeId) {
        return $this->users()
            ->wherePivot('user_type_id', $userTypeId);
    }

    public function creators() {
        $userTypeId = UserType::firstWhere('name', 'creator')?->id;
        return $this->relatedUsers($userTypeId);
    }

    public function executors() {
        $userTypeId = UserType::firstWhere('name', 'executor')?->id;
        return $this->relatedUsers($userTypeId);
    }

    public function coexecutors() {
        $userTypeId = UserType::firstWhere('name', 'coexecutor')?->id;
        return $this->relatedUsers($userTypeId);
    }

    public function controllers() {
        $userTypeId = UserType::firstWhere('name', 'controller')?->id;
        return $this->relatedUsers($userTypeId);
    }

    public function state() {
        return $this->belongsTo(HistoryType::class, 'state');
    }

    public static function index(Request $request) {
        $tasks = null;
        // return 'foo';
        switch ($request->input('view','notes')) {
            case 'create':
                $tasks = $request->user()
                  ->tasksCreating()
                  ->lazy();
                break;
            case 'execute':
                $tasks = $request
                  ->user()
                  ->tasksExecuting()
                  ->lazy();
                break;
            case 'coexecute':
                $tasks = $request
                  ->user()
                  ->tasksCoexecuting()
                  ->lazy();
                break;
            case 'control':
                $tasks = $request->user()->can(['taskman_dep_secretary'])?
                    Task::where('is_note', false)
                      ->lazy()
                      ->filter(function($item) { return $item->isDepSecretary(); })
                    :
                    $request
                      ->user()
                      ->tasksControlling()
                      ->lazy();
                break;
            case 'notes':
                $tasks = $request->user()->notes()->lazy();
                break;
            case 'all':
                $request->user()->can(['taskman_read_tasks'])?
                    $tasks = Task::where('is_note', false)
                        ->whereNotNull('deadline_at')
                        ->with('document')
                        ->lazy()
                    : abort(403);
                break;
            default:
                $tasks = $request->user()->notes()->lazy();
        }

        return Task::filter($request, $tasks);
    }

    // public static function indexAll(Request $request) {
    //     return Task::filter(
    //         $request,
    //         Task::where('is_note', false)
    //     );
    // }

    public static function filter(Request $request, $tasks) {
        $tasks = $tasks->filter(function ($value, $key) use ($request) {

            /** показывать поручения в работе */
            if (
                !$request->boolean('showInProgress', true) &&
                !($request->boolean('showClosed', false) && $value->closed_at != null) &&
                ($value->deadline_at > now() || $value->deadline_at == null)
            ) return false;

            /** показывать просроченные поручения */
            if (
                !$request->boolean('showExpired', true) &&
                !($request->boolean('showClosed', false) && $value->closed_at != null) &&
                $value->deadline_at <= now() &&
                $value->deadline_at != null
            ) return false;

            /** показывать закрытые поручения */
            if (
                !$request->boolean('showClosed', false) &&
                $value->closed_at != null
            ) return false;

            /** фильтр по тексту поручения */
            if (
                $request->filled('search') &&
                !Str::contains(Str::lower($value->body), Str::lower($request->input('search')))
            ) return false;

            /** фильр по заголовку или id проекта */
            if ($request->filled('project')) {
                if (Str::of($request->input('project'))->is('id:*')) {
                    $id = Str::substr($request->input('project'), 3);
                    if ($value->project?->id != $id) return false;
                } else {
                    if (!Str::contains(Str::lower($value->project?->title), Str::lower($request->input('project')))) return false;
                }
            }

            /** фильтр по исполнителю */
            if ($request->filled('user')) {
                foreach ($value->executors as $user) {
                    if (Str::of($request->input('user'))->is('id:*')) {
                        $id = Str::substr($request->input('user'), 3);
                        if ($user->id == $id) return true;
                    } else {
                        if (Str::contains(Str::lower($user->fullname), Str::lower($request->input('user')))) return true;
                    }
                }

                return false;
            }

            /** фильтр по важности */
            if ($request->filled('importance') && $value->importance != $request->input('importance')) return false;
            
            return true;
        })
        ->sortByDesc('created_at');
    
        if (!$request->boolean('all')) $tasks = $tasks->slice(($request->input('page', 1) - 1) * 20, 20);
        
        return $tasks->values();
    }

    public static function modify(Request $request) {
        Task::validateUsers($request);

        // if ($request->filled('project_id') && Project::findOrFail($request->input('project_id'))->user->id != $request->user()->id)
        //     abort(403, 'Вы не можете добоавлять поручения в данный проект');
        Task::isUserProject($request->input('project_id', null));

        $tasks = [];
        if ($request->boolean('is_individual')) {
            foreach ($request->input('executors') as $executor) {
                $tasks[] = Task::add($request, [ $executor ]);
            }
        } else {
          $tasks =  [ Task::add($request, $request->input('executors')) ];
        }

        /** привязываем к родительскому поручению */
        if ($request->filled('task_id')) {
          $parentTask = Task::find($request->input('task_id'));

          foreach($tasks as $task) {
            $task->task()->associate($parentTask);
            $task->save();
          }
        }

        return $tasks;
    }

    public static function add(Request $request, $executors = []) {
        $task = null;
        DB::transaction(function() use ($request, $executors, &$task){
            $input = $request->all();

            // если создатель один, а также не назначены исполнители, соисполнители и контроллеры,
            // то поручение считается заметкой
            $isNote = count($input['creators'] ?? []) == 0 &&
                !isset($executors) && !isset($input['coexecutors']) && !isset($input['controllers']);

            $historyTypeCreate = HistoryType::firstWhere('name', 'create')->id;

            $task = Task::create([
                'body' => $input['body'],
                'deadline_at' => $input['deadline_at'] ?? null,
                'is_note' => $isNote,
                'state' => $historyTypeCreate,
                'project_id' => $request->input('project_id', null),
                'importance' => $request->input('importance', 0),
            ]);

            $task->syncUsers(
                creators: $input['creators'] ?? [$request->user()->id],
                executors: $executors ?? [],
                coexecutors: $input['coexecutors'] ?? [],
                controllers: $input['controllers'] ?? [],
            );
            $task->markAsRead();

            Task::storeAttachments($task, $input['attachments'] ?? null);
        });

        /** TEMP */
        $history = $task?->histories()->create([
            'user_id' => $request->user()->id,
            'history_type_id' => HistoryType::firstWhere('name', 'create')->id,
        ]);

        return $task;
    }

    public static function storeAttachments($object, $attachments) {
        if ($attachments != null) {
            foreach($attachments as $attachment) {
                $path = $attachment->store('attachments');

                if (!$path) throw new \Exception(__('error.not_uploaded'));
                $object->attachments()->create([
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                ]);
            }
        }
    }

    public function syncUsers($creators = [], $executors = [], $coexecutors = [], $controllers = []) {
        // DB::transaction(function() use ($creators, $executors, $coexecutors, $controllers) {
            if (count($creators) == 0)
                abort(422, __('error.no_creators'));
            if (count($executors) == 0 && (count($coexecutors) > 0 || count($controllers) > 0 ))
                abort(422, __('error.no_executors'));

            $isNote = count($creators) == 0 &&
                count($executors) == 0 && count($coexecutors) == 0 && count($controllers) == 0;

            $this->syncCreators($creators);
            if (!$isNote) {
                $this->syncExecutors($executors);
                $this->syncCoexecutors($coexecutors);
                $this->syncControllers($controllers);
            }
        // });
    }

    public function syncCreators($users) {
        $this->creators()->syncWithPivotValues($users,
            ['user_type_id' => UserType::firstWhere(['name' => 'creator'])->id]
        );
    }

    public function syncExecutors($users) {
        if (count($users ?? []) == 0) $this->update(['is_note' => true]);
        else $this->update(['is_note' => false]);

        $this->executors()->syncWithPivotValues($users,
            ['user_type_id' => UserType::firstWhere(['name' => 'executor'])->id]
        );
    }

    public function syncCoexecutors($users) {
        $this->coexecutors()->syncWithPivotValues($users,
            ['user_type_id' => UserType::firstWhere(['name' => 'coexecutor'])->id]
        );
    }

    public function syncControllers($users) {
        $this->controllers()->syncWithPivotValues($users,
            ['user_type_id' => UserType::firstWhere(['name' => 'controller'])->id]
        );
    }

    public function updateUsers(Request $request) {
        Task::validateUsers($request, $this);
        // DB::transaction(function() use ($request) {
            $view = $request->input('view');
            
            $this->syncUsers(
                creators: $request->input('creators') ?? [$request->user()->id],
                executors: $request->input('executors') ?? [],
                coexecutors: $request->input('coexecutors') ?? [],
                controllers: $request->input('controllers') ?? [],
            );

            $this->histories()->create([
                'user_id' => $request->user()->id,
                'history_type_id' => HistoryType::firstWhere('name', $view)->id,
            ]);
        // });
    }

    public function updateDeadline(Request $request) {
        if ($request->boolean('request')) {
            $this->update([
                'request_deadline_at' => $request->input('request_deadline_at'),
            ]);

            $this->histories()->create([
                'user_id' => $request->user()->id,
                'history_type_id' => HistoryType::firstWhere('name', 'deadline_request')->id,
            ]);
        } else {
            if ($request->boolean('accept')) {
                if (now() > $this->request_deadline_at)
                abort(422, __('validation.after_or_equal'));
                if ($this->request_deadline_at == null)
                    abort(422, __('error.has_no_request_deadline'));

                $this->update([
                    'deadline_at' => $this->request_deadline_at,
                    'request_deadline_at' => null,
                ]);

                $this->histories()->create([
                    'user_id' => $request->user()->id,
                    'history_type_id' => HistoryType::firstWhere('name', 'deadline_updated')->id,
                ]);
            } else {
                $this->update([
                    'request_deadline_at' => null,
                ]);
                $this->histories()->create([
                    'user_id' => $request->user()->id,
                    'history_type_id' => HistoryType::firstWhere('name', 'request_deadline_declined')->id,
                ]);
            }
            
        }
    }

    public function deadlineIsClose() : Attribute {
        return new Attribute(
            get: function ($value, $attributes) {
                if (isset($attributes['deadline_at'])) {
                    $now = now();
        
                    if ($now->diffInSeconds($attributes['deadline_at'], false) < 0) return 3;
                    if ($now->diffInSeconds($attributes['deadline_at'], false) <= config('app.deadline_high') * 24 * 3600) return 2;
                    if ($now->diffInSeconds($attributes['deadline_at'], false) <= config('app.deadline_low') * 24 * 3600) return 1;
                }
        
                return 0;
            }
        );
    }

    public function daysTillDeadline() : Attribute {
        return new Attribute(
            get: function ($value, $attributes) {
                if (isset($attributes['deadline_at'])) {
                    $now = now();
        
                    return $now->diffInDays($attributes['deadline_at']);
                }
        
                return null;
            }
        );
    }

    public function isUnread() : Attribute {
        return new Attribute(
            get: function($value) {
                return auth()->user()?->tasks()
                    ->wherePivot('task_id', $this->id)
                    ->wherePivotNull('read_at')->count() > 0;
            }
        );
    }

    public function userChildTasks() : Attribute {
      //!!! если под документоведом или адиминов не отображаются свои заметки
      return new Attribute(get: function() {
        return $this->userChildTasksFn();
      });
    }

    public function userChildTasksCount() : Attribute {
      return new Attribute(get: function () {
        return count($this->userChildTasksFn() ?? []);
      });
    }

    public function userChildTasksFn() {
      //!!! если под документоведом или адиминов не отображаются свои заметки
      if (auth()->user() == null) return null;
      // return auth()->user()->can(['taskman_read_tasks'])? // если права для чтения всех задач?
      //   $this->tasksWithoutNotes()->get() // получаем все задачи
      //   : $this->tasks // получаем только задачи пользователя
      //   ->filter(function($item, $key) {
      //     return $item->isUserInTeam(auth()->user());
      //   })
      //   ->values();
      return $this->tasks // получаем только задачи пользователя
        ->filter(function($item, $key) {
          return $item->isUserInTeam(auth()->user());
        })
        ->values();
    }

    public function complete(Request $request) {
        $actualHistoryType = HistoryType::firstWhere('name', 'complete');
        if ($actualHistoryType->id == $this->state) abort(422, __('error.already_completed'));

        $history = null;
        DB::transaction(function() use ($request, &$history, $actualHistoryType) {
            $this->update([
                'state' => $actualHistoryType->id,
            ]);

            $history =  $this->histories()->create([
                'user_id' => $request->user()->id,
                'history_type_id' => $actualHistoryType->id,
            ]);
        });
        
        return $history;
    }

    public function back(Request $request) {
        $actualHistoryType = HistoryType::firstWhere('name', 'back');
        if ($actualHistoryType->id == $this->state) abort(422, __('error.already_back'));

        $history = null;
        DB::transaction(function() use ($request, &$history, $actualHistoryType) {
            $this->update([
                'state' => $actualHistoryType->id,
            ]);

            $history =  $this->histories()->create([
                'user_id' => $request->user()->id,
                'history_type_id' => $actualHistoryType->id,
            ]);
        });

        return $history;
    }

    public function comment(Request $request) {
        if (!is_null($this->closed_at)) abort(422, __('error.closed_comment'));

        $history = null;
        DB::transaction(function () use ($request) {
            $user = $request->user();

            $comment = $this->comments()->create([
                'body' => $request['comment'] ?? null,
                'user_id' => $user->id
            ]);

            $history =  $this->histories()->create([
                'history_type_id' => HistoryType::where('name', 'comment')->first()->id,
                'user_id' => $user->id
            ]);
            
            Task::storeAttachments($comment, $request->file('attachments') ?? null);
        });
        
        return $history;
    }

    public function close(Request $request) {
        $actualHistoryType = HistoryType::firstWhere('name', 'close');
        if ($actualHistoryType->id == $this->state) abort(422, __('error.already_closed'));

        DB::transaction(function() use ($request, $actualHistoryType) {
          // закрываем все дочерние поручения
          $fn = function($t) use (&$fn, $request, $actualHistoryType) {
            if ($t->closed_at === null) {
              $t->update([
                'closed_at' => now(),
                'state' => $actualHistoryType->id,
              ]);
  
              $t->histories()->create([
                  'user_id' => $request->user()->id,
                  'history_type_id' => $actualHistoryType->id, 
              ]); 
            }

            foreach($t->tasks as $childTask) {
              $fn($childTask);
            }
          };

          $fn($this);
          
        });
    }

    public function markAsRead() {
        auth()->user()->tasks()
            ->wherePivotNull('read_at')
            ->updateExistingPivot($this->id, ['read_at' => now()]);
    }

    public function markAsUnread() {
        foreach($this->users->except([auth()->user()->id]) as $user) {
            $user->tasks()
                // ->wherePivot('unread', false)
                ->updateExistingPivot($this->id, ['read_at' => null]);
        }
    }

    public static function counters(Request $request) {
        $user = $request->user();

        $creating = $user->tasksCreating()->whereNull('closed_at');
        $executing = $user->tasksExecuting()->whereNull('closed_at');
        $coexecuting = $user->tasksCoexecuting()->whereNull('closed_at');
        $controlling = $user->tasksControlling()->whereNull('closed_at');
        $notes = $user->notes()->whereNull('closed_at');

        return [
            'creating' => [
                'total' => $creating->count(),
                'unread' => $creating->wherePivotNull('read_at')->count(),
            ],
            'executing' => [
                'total' => $executing->count(),
                'unread' => $executing->wherePivotNull('read_at')->count(),
            ],
            'coexecuting' => [
                'total' => $coexecuting->count(),
                'unread' => $coexecuting->wherePivotNull('read_at')->count(),
            ],
            'controlling' => [
                'total' => $controlling->count(),
                'unread' => $controlling->wherePivotNull('read_at')->count(),
            ],
            'notes' => [
                'total' => $notes->count(),
            ],
            'mails' => [
                'total' => $user->mails()->wherePivotNull('read_at')->count(),
            ],
            'outgoingMails' => [
                'total' => $user->outgoingMails()->wherePivotNull('read_at')->count(),
            ],
            'decrees' => [
                'total' => $user->decrees()->wherePivotNull('read_at')->count(),
            ],
            'ksDecrees' => [
                'total' => $user->ksDecrees()->wherePivotNull('read_at')->count(),
            ],
            'orders' => [
                'total' => $user->orders()->wherePivotNull('read_at')->count(),
            ],
            'miscdocuments' => [
                'total' => $user->miscdocuments()->wherePivotNull('read_at')->count(),
            ],
        ];
    }

    public static function validateUsers(Request $request, Task $task = null) {
        $user = $request->user();

        if ($request->collect('creators')->intersect($request->collect('executors'))->isNotEmpty())
            abort(422, 'Постановщики присутствуют в списке исполнителей');

        if ($request->collect('creators')->intersect($request->collect('coexecutors'))->isNotEmpty())
            abort(422, 'Постановщики присутствуют в списке соисполнителей');

        if ($request->collect('creators')->intersect($request->collect('controllers'))->isNotEmpty())
            abort(422, 'Постановщики присутствуют в списке наблюдателей');

        if ($request->collect('executors')->intersect($request->collect('coexecutors'))->isNotEmpty())
            abort(422, 'Исполнители присутствуют в списке соисполнителей');

        if ($request->collect('executors')->intersect($request->collect('controllers'))->isNotEmpty())
            abort(422, 'Исполнители присутствуют в списке наблюдателей');

        if ($request->collect('coexecutors')->intersect($request->collect('controllers'))->isNotEmpty())
            abort(422, 'Соисполнители присутствуют в списке наблюдателей');

        if ($task != null && $task->executors->contains($user)) {
            if (!Task::isEqualCollections($task->creators->pluck('id'), $request->collect('creators')) ||
                !Task::isEqualCollections($task->executors->pluck('id'), $request->collect('executors')) ||
                !Task::isEqualCollections($task->controllers->pluck('id'), $request->collect('controllers')))
                abort(403, 'Вы можете менять только список соисполнителей');
        }

        $check = function ($users1, $users2, $alert) use ($user){
            if ($user->can('taskman_modify_tasks')) return;

            foreach($users1 as $creator)
                $creator = User::find($creator);
                foreach($users2 as $executor) {
                    $executor = User::find($executor);

                    if (!$creator->is_leader) abort(422, 'Постановщик не является руководителем');
                    if ($creator->department->childrenIds()->doesntContain($executor->department->id))
                        abort(422, $alert);
                }
        };

        $check($request->collect('creators'), $request->collect('executors'),
            'Постановщики не могут назначать поручения указанным сотрудникам');
        $check($request->collect('creators'), $request->collect('coexecutors'),
            'Постановщики не могут назначать указанных сотрудников в качестве соисполнителей');
    }

    public static function isEqualCollections($arr1, $arr2) {
        return $arr1->diff($arr2)->count() == 0 && $arr1->count() == $arr2->count();
    }

    /** проверяем, может ли ползователь создавать поручение в проекте */
    public static function isUserProject($projectId) {
        if ($projectId != null && Project::findOrFail($projectId)->user->id != auth()->user()->id)
            abort(403, 'Вы не можете добавлять поручения в данный проект');
    }

    /**
     * Проверяем принадлежность пользователя к группа для редактирования: создатели, исполнители, соисполнители
     */
    public function isUserInEditorsTeam(User $user) {
      return $this->creators->contains($user) ||
        $this->executors->contains($user) ||
        $this->coexecutors->contains($user);
    }

    /**
     * Проверяем принадлежность пользователя ко всем группам поручения
     */
    public function isUserInTeam(User $user) {
      return $this->isUserInEditorsTeam($user) ||
        $this->controllers->contains($user);
    }

    /**
     * Проверяем принадлежность пользователя ко всем дочерним поручениям
     */
    public function isUserInChildTasks(User $user) {
      $result = false;
      $check = function($tasks) use (&$result, $user, &$check) {
        if ($tasks->some(function($item, $key) use ($user, &$check) {
          $check($item->tasks);

          return $item->isUserInTeam($user);
        })) $result = true;
      };

      $check($this->tasks);

      return $result;
      // return $this->tasks->some(function($item, $key) use ($user) {
      //   return $item->isUserInTeam($user);
      // });
    }

    public static function report(Request $request) {
        $users = [];
        foreach($request->user()->tasksCreating()->whereNull('closed_at')->get() as $task) {
            foreach($task->executors as $user) {
                $current = $users[$user->id] ?? ['shortname' => $user->shortname, 'executing' => 0, 'expired' => 0];
                $users[$user->id] = [
                    'user_id' => $user->id,
                    'shortname' => $user->shortname,
                    'executing' => $current['executing'] + ($task->deadline_is_close != 3? 1 : 0),
                    'expired' => $current['expired'] + ($task->deadline_is_close == 3? 1 : 0),
                ];
            }
        }

        return collect($users)->sortBy('shortname')->values();
    }

    public function attachProject(Project $project = null) {
        Task::isUserProject($project?->id);

        return $this->update(['project_id' => $project?->id]);//auth()->user()->save($project);
    }

    public function isDepSecretary() : bool {
        $user = auth()->user() ?? null;
        if (!$user->can(['taskman_dep_secretary'])) return false;

        $departmentId = $user->department_id;
        return $this->users->some(function($user) use ($departmentId) {
            return $user->department_id == $departmentId;
        });
    }
}