<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Task;
use App\Notifications\GeneralNotification;

class TaskRemainder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function broadcastOn()
    {
        // $privateChannels = [];

        // foreach($this->task->creators as $user)
        //     $privateChannels[] = new PrivateChannel('events.' . $user->id);
        // foreach($this->task->executors as $user)
        //     $privateChannels[] = new PrivateChannel('events.' . $user->id);
        // foreach($this->task->coexecutors as $user)
        //     $privateChannels[] = new PrivateChannel('events.' . $user->id);
        // foreach($this->task->controllers as $user)
        //     $privateChannels[] = new PrivateChannel('events.' . $user->id);

        $titles = [
            1 => 'Обратите внимание на срок поручения',
            2 => 'Срок поручения истекает',
            3 => 'Поручение не выполнено',
        ];

        foreach($this->task->users as $user)
            if ($this->task->deadline_is_close > 0)
                $user->notify(new GeneralNotification([
                    'title' => $titles[$this->task->deadline_is_close],
                    'message' => $this->task->body,
                    'link' => 'http://unisys-taskman.mkvityaz.ru/#/task/' . $this->task->id,
                ]));

        // return $privateChannels;
    }
}
