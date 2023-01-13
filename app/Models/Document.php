<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'type_id',
        'number',
        'issued_at',
        'body',
        'kind',
        // 'is_outgoing',
        // 'is_kadr_salary',
        'sent_at',
        'sent_by',
        'partner',
        'signer',
        'executor',
        'signer_manual',
        'inner_number',
    ];

    protected $with = [
      'type',
      'attachments',
      'senders',
      // 'mailingUsers',
      // 'approvalUsers'
    ];

    protected $casts = ['created_at' => 'datetime:d.m.Y', 'issued_at' => 'datetime:d.m.Y', 'sent_at' => 'datetime:d.m.Y'];

    protected $appends = ['issuer', 'is_unread', 'is_approved', 'fullname', 'user_tasks', 'issued_at_iso', 'sent_at_iso'];

    public static function boot() {
        parent::boot();

        static::deleting(function($item) {
            $item->attachments()->each(function($attachment) {
                Storage::delete($attachment->path);
            });
        });
    }

    public function type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function users() {
        return $this
            ->belongsToMany(User::class, config('database.connections.mysql.database').'.document_user', 'document_id', 'user_id')
            ->using(DocumentUser::class)
            ->withPivot('is_mailing')
            ->withPivot('read_at');
    }

    public function senders() {
        return $this->users()->wherePivot('is_sender', true);
    }

    public function approvalUsers() {
        return $this->users()->wherePivotNotNull('approval_order')->orderBy('approval_order')
            ->withPivot('approval_order', 'approval_note', 'is_approved');
    }

    public function mailingUsers() {
        return $this->users()->wherePivot('is_mailing', true)
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->orderBy('middlename');
    }

    /** все поручения по документу */
    public function tasks() {
        return $this->hasMany(Task::class);
    }

    /** все поручения по документу без заметок */
    public function tasksWithoutNotes() {
      return $this->hasMany(Task::class)->where('is_note', false);
  }

    /** поручения по документу текущего пользователя */
    // public function userTasks() {
    //     // return $this->hasMany(Task::class);
        
    // }

    public function histories() {
        return $this->hasMany(History::class);
    }

    public function attachments() {
        return $this->hasMany(Attachment::class);
    }

    public function userTasks() : Attribute {
        return new Attribute(get: function() {
            if (auth()->user() == null) return null;
            return auth()->user()->can(['taskman_read_tasks'])? // если права для чтения всех задач?
                $this->tasksWithoutNotes()->get() // получаем все задачи
                : $this->tasks() // получаем только задачи пользователя
                ->whereIn('id', auth()->user()->tasks()->pluck('tasks.id'))
                ->get();
        });
    }

    public function issuer() : Attribute {
        return new Attribute(get: function() {
            return $this->users()->wherePivot('is_issuer', true)->first();
        });
    }

    public function fullname(): Attribute {
        return new Attribute(get: function() {
            return $this->type?->title . '  №' . $this->number . ' от ' . $this->issued_at?->format('d.m.Y'); 
        });
    }

    public function isUnread() : Attribute {
        return new Attribute(get: function() {
            return $this->mailingUsers()
                ->wherePivot('user_id', auth()->user()->id)
                ->wherePivotNull('read_at')->count() > 0;
        });
    }

    public function isApproved() : Attribute {
        return new Attribute(get: function() {
            $approvalStatus = $this->approvalUsers()->wherePivot('user_id', auth()->user()->id);
            return $approvalStatus->count() > 0? $approvalStatus->first()?->pivot->is_approved : -1;
        });
    }

    public function issuedAtIso() : Attribute {
        return new Attribute(get: function() {
            return $this->issued_at?->format('Y-m-d');
        });
    }

    public function sentAtIso() : Attribute {
        return new Attribute(get: function() {
            return $this->sent_at?->format('Y-m-d');
        });
    }

    public static function index(Request $request) {
        $documents = null;
        // делопроизводители видят все документы
        if ($request->user()->can(['taskman_read_documents']))
            $documents = Document::lazy();
        // секретари подразделений - все документы только своего подразделения
        else if ($request->user()->can(['taskman_dep_secretary'])) {
            $documents = Document::lazy();
            // $department = $request->user()->department;
            $documents = $documents->filter(function($item, $key) use ($request) {
                return $item->isDepSecretary();
            });
        }
        // остальные - только свои
        else $documents = $request->user()->documents()->lazy();

        if ($request->input('sortHow', 'desc') == 'asc')
            $documents = $documents->sortBy($request->input('sortBy', 'issued_at'));
        else
            $documents = $documents->sortByDesc($request->input('sortBy', 'issued_at'));

        return $documents
            ->where('type_id', DocumentType::firstWhere('name_plural', $request->input('view', 'miscdocuments'))->id)
            ->filter(function ($value, $key) use ($request) {
                /** не показываем ознакомленные */
                if (!$request->boolean('showRead', true) && !$value->isUnread) return false;

                /** фильтр по всем аттрибутам через быстрый поиск */
                if ($request->filled('search')) {
                    foreach (Str::of(Str::lower(Str::squish($request->input('search'))))->explode(' ') as $search) {
                        if (!(
                            Str::contains(Str::lower($value->number), $search) ||
                            Str::contains(Str::lower($value->body), $search) ||
                            Str::contains(Str::lower($value->kind), $search) ||
                            Str::contains(Str::lower($value->signer), $search) ||
                            Str::contains(Str::lower($value->executor), $search) ||
                            Str::contains(Str::lower($value->sent_by), $search) ||
                            Str::contains(Str::lower($value->partner), $search) ||
                            Str::contains(Str::lower($value->issuer?->fullname), $search) ||
                            Str::contains(Str::lower($value->inner_number), $search) ||
                            Str::contains(Str::lower($value->signer_manual), $search)
                        )) return false;
                    }
                }
                

                /** фильр по периоду дат */
                if (
                    ($request->filled('dateFrom') && $request->filled('dateTo')) &&
                    !($value->issued_at >= $request->date('dateFrom') && $value->issued_at <= $request->date('dateTo'))
                ) return false;

                return true;
            })
            // ->sortByDesc('created_at')
            ->slice(($request->input('page', 1) - 1) * 20, 20)
            ->values();
    }

    public static function store(Request $request) {
        $document = null;
        DB::transaction(function() use ($request, &$document) {
            /** проверяем уникальность номера документа */
            if ($request->input('type', 'miscdocument') !== 'mail')
            if (Document::where('type_id', DocumentType::firstWhere('name', $request->input('type', 'miscdocument'))->id)
                ->where('number', $request->string('number', null))
                ->count() > 0)
                abort(422, 'Указанный номер уже существует для данного типа документа');
            
            $document = Document::create([
                'type_id' => DocumentType::firstWhere('name', $request->input('type', 'miscdocument'))->id,
                'number' => $request->filled('number')? $request->string('number', null) : null,
                'issued_at' => $request->filled('issued_at')? $request->date('issued_at', null) : null,
                'body' => $request->filled('body')? $request->string('body', null) : null,
                
                'inner_number' => $request->filled('inner_number') && $request->input('type', 'miscdocument') == 'mail'?
                    $request->input('inner_number', null) : null,
                'kind' => $request->filled('kind')?
                    $request->input('type', 'miscdocument') == 'outgoingMail' ||
                    $request->input('type', 'miscdocument') == 'miscdocument'?
                    $request->string('kind', null) : null : null,
                // 'is_outgoing' => $request->input('type', 'miscdocument') == 'mail'? $request->boolean('is_outgoing', null) : null,
                // 'is_kadr_salary' => $request->input('type', 'miscdocument') == 'decree'? $request->boolean('is_kadr_salary', null) : null,
                'sent_at' => $request->filled('sent_at')?
                    $request->input('type', 'miscdocument') == 'outgoingMail'?
                    $request->date('sent_at', null) : null : null,
                'sent_by' => $request->filled('sent_by')?
                    $request->input('type', 'miscdocument') == 'outgoingMail'?
                    $request->string('sent_by', null) : null : null,
                'partner' => $request->filled('partner')?
                    $request->input('type', 'miscdocument') == 'mail' || $request->input('type', 'miscdocument') == 'outgoingMail'?
                    $request->string('partner', null) : null : null,
                'signer_manual' => $request->filled('signer_manual') && $request->input('type', 'miscdocument') == 'mail'?
                    $request->string('signer_manual', null) : null,
                'signer' => $request->input('type', 'miscdocument') != 'mail' &&
                    $request->input('type', 'miscdocument') != 'poa'? $request->input('signer', null) : null,
                'executor' => $request->input('type', 'miscdocument') != 'mail'?
                    $request->input('executor', null) : null,
            ]);

            // $document->users()->syncWithPivotValues(
            //     $request->input('senders', []),
            //     ['is_sender' => true,]
            // );
            $document->users()->syncWithPivotValues(
                Arr::where($request->input('mailing_users', []), fn($value, $key) => $value != $request->user()->id),
                ['is_mailing' => true,]
            );

            // foreach($request->input('approval_users', []) as $k => $users) {
            //     $users = explode(':', $users);
            //     $document->users()->syncWithPivotValues(
            //         $users,
            //         ['approval_order' => $k],
            //     );
            // }
            $document->users()->syncWithPivotValues(
                $request->user(),
                ['is_issuer' => true, 'read_at' => now()]
            );

            if ($request->hasFile('attachments')) foreach ($request->file('attachments') as $attachment) {
                $path = $attachment->store('attachments');

                $document->attachments()->create([
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                ]);
            }
        });

        $document->histories()->create([
            'user_id' => $request->user()->id,
            'history_type_id' => HistoryType::firstWhere('name', 'document_created')->id,
        ]);

        return $document;
    }

    public function markAsRead() {
        auth()->user()->documents()
            ->wherePivotNull('read_at')
            ->updateExistingPivot($this->id, ['read_at' => now()]);
    }

    /** Секретарь имеет доступ к документу подразделения */
    public function isDepSecretary() : bool {
        $user = auth()->user() ?? null;
        if (!$user->can(['taskman_dep_secretary'])) return false;

        $departmentId = $user->department_id;
        return $this->mailingUsers->some(function($user) use ($departmentId) {
            return $user->department_id == $departmentId;
        });
    }

    public function approve(Request $request) {
        $this->approvalUsers()
            ->updateExistingPivot(auth()->user()->id, [
                'is_approved' => $request->boolean('approve'),
                'approval_note' => $request->input('note', null),
            ]);
        // $this->approvalUsers()
        //     ->wherePivotNull('read_at')
        //     ->updateExistingPivot(auth()->user()->id, ['read_at' => now()]);
    }

    /**
    * Проверяем состояние согласования и, если один этап полностью закрыт,
    * то отправляем уведомление всем участникам следующего этапа согласования
    */
    public function checkApproveState() {
        $groups = $this->approvalUsers->map(function($value, $key) {
            return [
                'id' => $value->id,
                'is_approved' => $value->pivot->is_approved,
                'approval_order' => $value->pivot->approval_order,
            ];
        })
        // ->sortBy('approval_order')
        ->groupBy(function($value, $key) { return $value['approval_order']; });

        $order = 0; // никто не согласовал
        foreach($groups as $key => $group) {
            if ($group->every(function ($value) {
                return $value['is_approved'];
            })) $order = $key + 1; // весь этап согласован
            else break;
        }

        return $order;
    }

    public function attachTask(Request $request) {
        DB::transaction(function() use ($request) {
            foreach($request->input('tasks') as $taskId) {
                $this->tasks()->save(Task::findOrFail($taskId));
            }
        });
    }
}
