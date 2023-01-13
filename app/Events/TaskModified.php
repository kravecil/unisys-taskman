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
use App\Models\HistoryType;
use App\Notifications\GeneralNotification;

class TaskModified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $action;
    public $historyType;
    public $userId;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($task, $action = null, $historyType = null)
    {
        $this->task = $task;
        $this->action = $action;
        $this->userId = auth()->user()->id ?? null; //temp
        $this->user = auth()->user();
        $this->historyType = $historyType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $privateChannels = [];

        foreach($this->task->creators as $user)
            $privateChannels[] = new PrivateChannel('events.' . $user->id);
        foreach($this->task->executors as $user)
            $privateChannels[] = new PrivateChannel('events.' . $user->id);
        foreach($this->task->coexecutors as $user)
            $privateChannels[] = new PrivateChannel('events.' . $user->id);
        foreach($this->task->controllers as $user)
            $privateChannels[] = new PrivateChannel('events.' . $user->id);

        foreach($this->task->users as $user) {
            if ($user->id == $this->user->id) continue;
            
            $user->notify(new GeneralNotification([
                'title' => $this->historyType?->title,
                'message' => $this->task->body,
                'link' => 'http://unisys-taskman.mkvityaz.ru/#/task/' . $this->task->id,
            ]));
        }

        // $privateChannels[] = new PrivateChannel('notifications' . $user->id)
        
        return $privateChannels;
        // return new PrivateChannel('events.3');
    }
}
