<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\History;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function isUserInEditorsTeam(User $user, Task $task) { // temp
        return $task->isUserInEditorsTeam($user);
    }

    public function isUserInTeam(User $user, Task $task) { // temp
        return $task->isUserInTeam($user);
    }

    public function isUserInChildTasks(User $user, Task $task) { // temp
      return $task->isUserInChildTasks($user);
    }

    public function viewAnyComment(User $user, Task $task) {
        return $this->isUserInTeam($user, $task) ||
          $this->isUserInChildTasks($user, $task) ||
          $user->can(['taskman_read_tasks']) ||
          $task->isDepSecretary();
    }

    public function viewAnyHistory(User $user, Task $task) {
        return $this->isUserInTeam($user, $task) ||
          $this->isUserInChildTasks($user, $task) ||
          $user->can(['taskman_read_tasks']) ||
          $task->isDepSecretary();
    }

    public function viewAttachment(User $user, Task $task) {
        return $this->isUserInTeam($user, $task) ||
          $this->isUserInChildTasks($user, $task) ||
          $user->can(['taskman_read_tasks']) ||
          $task->isDepSecretary();
    }

    public function view(User $user, Task $task)
    {
      return $this->isUserInTeam($user, $task) ||
        $this->isUserInChildTasks($user, $task) ||
        $user->can(['taskman_read_tasks']) ||
        $task->isDepSecretary();
    }

    // public function create(User $user, Task $task = null)
    // {
    //     echo 'lol1';
    //     return true;//$user->can(['taskman_modify_tasks']) || $user->canSetTask($request);
    // }

    public function updateUsers(User $user, Task $task)
    {
        return $user->can(['taskman_modify_tasks']) || $task->creators->contains($user) ||
            $task->executors->contains($user);
    }

    public function updateDeadline(User $user, Task $task, Request $request)
    {
        if ($user->can(['taskman_modify_tasks'])) return true;
        
        if ($request->boolean('request'))
            return $this->isUserInEditorsTeam($user, $task);
        else return  $task->creators->contains($user);
    }

    public function delete(User $user, Task $task)
    {
        return $user->can(['taskman_modify_tasks']) || $task->creators->contains($user);
    }

    public function complete(User $user, Task $task)
    {
        // var_dump($task->executors->contains($user));
        return $user->can(['taskman_modify_tasks']) ||
            $task->executors->contains($user) ||
            ($task->isCoexecutors? $task->coexecutors->contains($user) : false);
    }

    public function back(User $user, Task $task)
    {
        return $user->can(['taskman_modify_tasks']) || $task->creators->contains($user);
    }

    public function comment(User $user, Task $task)
    {
        return $user->can(['taskman_read_tasks']) || $this->isUserInTeam($user, $task);
    }

    public function close(User $user, Task $task)
    {
        return $user->can(['taskman_modify_tasks']) || $task->creators->contains($user);
    }
}
