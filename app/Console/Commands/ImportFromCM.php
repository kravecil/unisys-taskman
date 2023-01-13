<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Task;
use App\Models\HistoryType;

class ImportFromCM extends Command
{
    protected $signature = 'import:cm';
    protected $description = 'Import data from ControlManager';

    public function handle()
    {
        /** сопоставим пользователей ЕИС и ControlManager */
        $bosses = DB::connection('controlmanager')
            ->table('todos')
            ->select(['created_at', 'title', 'deadline_date'])
            ->whereNull('done_date')
            ->whereNull('deleted_at')
            ->where(function($query) {
                $query->where('person_id', 38);
                $query->orWhere('person_id', 1108);
                $query->orWhere('person_id', 2505);
                $query->orWhere('person_id', 2920);
            })
            ->get();
        
        $executor = User::where('username', 'n.zubarev')->first();
        $creator = User::where('username', 'i.arslanov')->first();
        foreach($bosses as $boss) {
            $task = Task::create([
                'body' => $boss->title,
                'created_at' => $boss->created_at,
                'deadline_at' => $boss->deadline_date,

                'is_note' => false,
                'state' => HistoryType::firstWhere('name', 'create')->id,
            ]);

            $task->syncCreators([$creator->id]);
            $task->syncExecutors([$executor->id]);
        };
        
        return 0;
    }
}
