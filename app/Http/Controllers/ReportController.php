<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;
use App\Models\Document;

class ReportController extends Controller
{
    public function tasks(Request $request) {
        if (!$request->user()->can('taskman_read_tasks')) abort(403);

        $request->validate([
            'datetime' => ['nullable', 'date'],
        ]);

        $result = [];
        $is_sum = $request->boolean('is_sum', false);
        $executor_id = $request->input('executor_id', null);
        $datetime = $request->date('datetime') ?? now(); //return $datetime;
        $hideClosed = $request->boolean('hide_closed', true);
        $counter = 0;
        $maxCounter = 0;

        $tasks = Task::with('document')
            // ->whereNull('closed_at')
            ->where('importance', 1)
            ->orWhere('importance', 2)
            ->lazy();
        foreach($tasks as $task) {
            // если указано пропускать исполненные поручения, пропускаем
            if ($hideClosed && $task->closed_at != null) continue;
            
            /** пропускаем поручения без сроков (не на контроле) */
            if ($task->deadline_at == null/*  || $task->deadline_is_close != 3 */) continue;

            // перебираем исполнителей в каждой из задач
            foreach($task->executors as $executor) {
                // если задан фильтр исполнителя и текущий исполнитель на попадает под фильтр, то пропускаем
                if ($executor_id && $executor_id != $executor->id) continue;

                /** если задана дата пропускаем
                * если срок исполнения ещё не наступил (больше $datetime)
                * если поручение закрыто до $datetime
                */
                if ($datetime && ($task->deadline_at >= $datetime || ($task->closed_at != null && $task->closed_at < $datetime))) continue;

                $result[$executor->id]['user'] = $executor;
                if ($is_sum) {
                    $result[$executor->id]['tasks'] = ($result[$executor->id]['tasks'] ?? 0) + 1;
                    if ($result[$executor->id]['tasks'] > $maxCounter) $maxCounter = $result[$executor->id]['tasks'];
                }
                else $result[$executor->id]['tasks'][$task->id] = $task;

                $counter++;
            }
        }

        uasort($result, function($a, $b) {
            return strcmp($a['user']['shortname'], $b['user']['shortname']);
        });

        if ($is_sum) return [
            'result' => array_values($result),
            'max_counter' => $maxCounter,
        ];

        return array_values($result);
    }
}