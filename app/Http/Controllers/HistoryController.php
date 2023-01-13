<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;

class HistoryController extends Controller
{
    public function index(Request $request, Task $task)
    {
        $this->authorize('viewAnyHistory', $task);
        
        return $task->histories()
            ->with('historyTypes')
            ->with('user')
            ->latest()
            ->get();
    }
}
