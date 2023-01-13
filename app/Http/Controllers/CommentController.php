<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;

class CommentController extends Controller
{
    public function index(Request $request, Task $task) {
        $this->authorize('viewAnyComment', $task);
        
        return $task->comments()
            ->with('attachments')
            ->with('user')
            ->latest()
            ->get();
    }
}
