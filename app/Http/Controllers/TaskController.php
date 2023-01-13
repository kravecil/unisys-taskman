<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\History;
use App\Models\HistoryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Project;
use App\Events\TaskShown;
use App\Events\TaskModified;

class TaskController extends Controller
{
    private const BODY_PATTERN = '/^[\w\d\s\-\'.?!)(,:;@\+"]+$/u';

    public function index(Request $request)
    {
        return Task::index($request);
    }

    // public function indexAll(Request $request)
    // {
    //     if ($request->user()->can(['taskman_read_tasks']))
    //         return Task::indexAll($request);
        
    //     abort(403);
    // }


    public function store(Request $request)
    {
        $request->validate([
            'body' => ['required', 'string', 'regex:' . TaskController::BODY_PATTERN],
            'deadline_at' => 'nullable|date|after_or_equal:now',
            'attachments.*' => ['nullable', 'max:50000000'],
        ]);

        $request->mergeIfMissing(['creators' => [$request->user()->id]]);

        // $this->authorize('mycreate', null);
        
        $task = Task::modify($request);

        return $task;
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $task->markAsRead();
        
        TaskShown::dispatch($task);

        return $task->loadMissing('document', 'task'/* , 'tasks' */)->append('user_child_tasks');
    }

    public function updateUsers(Request $request, Task $task) {
        $this->authorize('updateUsers', $task);

        return $task->updateUsers($request, $request->input('executors'));
    }

    public function updateDeadline(Request $request, Task $task) {
        $request->validate([
            'request_deadline_at' => 'nullable|date|after_or_equal:now',
        ]);

        $this->authorize('updateDeadline',  [$task, $request]);

        return $task->updateDeadline($request);
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        TaskModified::dispatch($task, 'destroyed'); // решить, надо ставить после удаления

        return $task->delete();
    }

    public function complete(Request $request, Task $task) {
        $this->authorize('complete', $task);

        return $task->complete($request);
    }

    public function back(Request $request, Task $task) {
        $this->authorize('back', $task);

        return $task->back($request);
    }
    
    public function comment(Request $request, Task $task) {
        $request->validate([
            'comment' => ['nullable', 'string', 'max:1000','regex:' . TaskController::BODY_PATTERN],
            'attachments.*' => ['nullable', 'max:50000000'],
        ]);

        $this->authorize('comment', $task);

        return $task->comment($request);
    }

    public function close(Request $request, Task $task) {
        $this->authorize('close', $task);

        return $task->close($request);
    }

    public function counters(Request $request) {
        return Task::counters($request); 
    }

    public function report(Request $request) {
        return Task::report($request);
    }

    public function attachProject(Request $request, Task $task, Project $project = null) {
        return $task->attachProject($project);
    }

    public function updateImportance(Request $request, Task $task) {
        if (!$request->user()->can(['taskman_modify_tasks'])) abort(403);

        return $task->update(['importance' => $request->input('importance', 0)]);
    }

    public function setRepeat(Request $request, Task $task) {
      if (!$task->creators->some($request->user())) abort(403, 'Вы не являетесь постановщиком!');

      return $task->update(['repeat_in' => $request->input('repeat_in', null)]);
    }
}
