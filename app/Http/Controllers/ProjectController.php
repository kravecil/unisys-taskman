<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\Task;

class ProjectController extends Controller
{
    public function index(Request $request) {
        return Project::index($request);
    }

    public function store(Request $request) {
        return Project::store($request);
    }

    public function show(Request $request, Project $project) {
        // return $request->user()->projects()->firstWhere('id', $project->id);
        return $project;
    }

    public function destroy(Request $request, Project $project) {
        if ($request->user()->projects()->where('id', $project->id)->count() == 0)
            abort(403, 'Нет прав для удаления данного проекта');

        return $project->delete();
    }

    public function showTasks(Request $request, Project $project) {
        return Task::filter($request, auth()->user()->tasks()->where('project_id', $project->id)->lazy());
    }
}
