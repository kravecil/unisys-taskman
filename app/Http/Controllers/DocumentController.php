<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Task;
use App\Models\HistoryType;

class DocumentController extends Controller
{
    public function index(Request $request) {
        $request->validate([
            'search' => ['nullable', 'string'],
            'dateFrom' => ['nullable', 'date'],
            'dateTo' => ['nullable', 'date'],
        ]);

        return Document::index($request);
    }

    // public function indexSenders(request $request) {
    //     return Document::where('sender', 'like', '%' . $request->input('search', '') . '%')
    //         ->distinct()
    //         ->orderBy('sender')
    //         ->pluck('sender')
    //         ->take(10);
    // }

    public function indexSentBy(Request $request) {
        return Document::where('sent_by', 'like', '%' . $request->input('search', '') . '%')
            ->distinct()
            ->orderBy('sent_by')
            ->pluck('sent_by')
            ->take(10);
    }

    public function indexPartners(Request $request) {
        return Document::where('partner', 'like', '%' . $request->input('search', '') . '%')
            ->distinct()
            ->orderBy('partner')
            ->pluck('partner')
            ->take(10);
    }
    public function indexKinds(Request $request) {
        return Document::where('kind', 'like', '%' . $request->input('search', '') . '%')
            ->distinct()
            ->orderBy('kind')
            ->pluck('kind')
            ->take(10);
    }
    public function indexSigners(Request $request) {
        return Document::where('signer_manual', 'like', '%' . $request->input('search', '') . '%')
            ->distinct()
            ->orderBy('signer_manual')
            ->pluck('signer_manual')
            ->take(10);
    }
    
    public function store(Request $request) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);
        $request->validate([
            // 'number' => ['nullable', 'string', 'max:20', /* 'regex:/^[\d\-\/]+$/u' */],
            'inner_number' => ['nullable', 'numeric'],
            'issued_at' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date'],
        ]);

        return Document::store($request);
    }

    public function destroy(Request $request, Document $document) {
        if ($request->user()->can(['taskman_modify_tasks']))
            if ($request->boolean('deleteTasks'))
                $document->tasks->each(function($item, $key) { $item->delete(); });

        if ($request->user()->can('taskman_modify_documents')) $document->delete();
    }

    public function show(Request $request, Document $document) {
        if (
            // пользователь в списке привязанных к документу (регистраторы, рассылка, ...)
            $document->users()->where('user_id', $request->user()->id)->count() == 0 &&
            // пользователь имеет право для чтения всех документов
            !$request->user()->can(['taskman_read_documents']) &&
            !$document->isDepSecretary() &&
            // пользователь в списке привязанных
            !$document->tasks()->lazy()->some(function($task, $key) use ($request) {
                // return $task->users()->lazy()->some(function($user, $userKey) use ($request){
                //     return $user->id == $request->user()->id;
                // });
                return $task->isUserInTeam($request->user()) || $task->isUserInChildTasks($request->user());
            })
        )
            abort(403, 'Отсутствуют права на просмотр данного документа');
        return $document->loadMissing('mailingUsers');
    }

    public function read(Request $request, Document $document) {
        $document->markAsRead();
    }

    public function approve(Request $request, Document $document) {
        $document->approve($request);
    }

    public function attachTask(Request $request, Document $document) {
        return $document->attachTask($request);
    }

    public function getLastNumber(Request $request) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);
        
        $type_id = DocumentType::where('name', $request->input('type', ''))->firstOrFail()?->id;
        // if ($type_id == null) abort(404);

        $number = Document::where('type_id', $type_id)
            // ->where(function($query) use ($request) {
            //     if ($request->input('type', '') == 'mail') $query->where('is_outgoing', $request->boolean('is_outgoing'));
            //     if ($request->input('type', '') == 'decree') $query->where('is_kadr_salary', $request->boolean('is_kadr_salary'));
            // })
            ->whereNotNull('number')
            ->whereNotNull('issued_at')
            ->orderByDesc('issued_at')
            ->orderByDesc('number')
            ->first()?->number ?? 0;

        return preg_replace('/\D/', '', $number) + 1;
    }

    /**TEMP: Dont Repeat Yourself!!!!!!!! */
    public function getLastInnerNumber(Request $request) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);

        $type_id = DocumentType::where('name', 'mail')->firstOrFail()?->id;
        // if ($type_id == null) abort(404);

        $inner_number = Document::where('type_id', $type_id)
            // ->where(function($query) use ($request) {
            //     if ($request->input('type', '') == 'mail') $query->where('is_outgoing', $request->boolean('is_outgoing'));
            //     if ($request->input('type', '') == 'decree') $query->where('is_kadr_salary', $request->boolean('is_kadr_salary'));
            // })
            ->whereNotNull('inner_number')
            ->orderByDesc('inner_number')
            ->first()?->inner_number ?? 0;

        return preg_replace('/\D/', '', $inner_number) + 1;
    }

    public function updateNumber(Request $request, Document $document) {
        if (!$request->user()->can('taskman_modify_documents')) abort(403);

        $request->validate([
            'number' => ['nullable', 'max:50'],
        ]);

        /** проверяем уникальность номера документа */
        if ($request->input('type', 'miscdocument') !== 'mail')
        if (Document::where('type_id', DocumentType::firstWhere('name', $request->input('type', 'miscdocument'))->id)
            ->where('number', $request->string('number', null))
            ->count() > 0)
            abort(422, 'Указанный номер уже существует для данного типа документа');

        $document->update(['number' => $request->filled('number')? $request->string('number') : null]);
        return $document;
    }

    public function updateIssuedAt(Request $request, Document $document) {
        if (!$request->user()->can('taskman_modify_documents')) abort(403);

        $request->validate([
            'issued_at' => ['nullable', 'date'],
        ]);

        $document->update(['issued_at' => $request->filled('issued_at')? $request->string('issued_at') : null]);
        return $document;
    }

    public function updateSent(Request $request, Document $document) {
        if (!$request->user()->can('taskman_modify_documents')) abort(403);

        $request->validate([
            'sent_at' => ['nullable', 'date'],
        ]);

        $document->update([
            'sent_at' => $request->input('sent_at'),
            'sent_by' => $request->input('sent_by'),
        ]);
        return $document;
    }

    public function updateMailingUsers(Request $request, Document $document) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);

        $result = $document->mailingUsers()->syncWithPivotValues(
            Arr::where($request->input('mailing_users', []), fn($value, $key) => $value != $request->user()->id),
            ['is_mailing' => true,]
        );

        $document->histories()->create([
            'user_id' => $request->user()->id,
            'history_type_id' => HistoryType::firstWhere('name', 'document_mailing_users_modified')->id,
        ]);

        return $result;
    }

    public function updateFields(Request $request, Document $document) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);

        $request->validate([
            'column' => ['required', 'string'],
            // 'value' => ['required', 'string'],
        ]);
        return $document->update([
            $request->input('column') => $request->input('value'),
        ]);
    }
}
