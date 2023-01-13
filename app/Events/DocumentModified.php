<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Document;
use App\Notifications\GeneralNotification;

class DocumentModified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $document;
    public $user;
    public $action;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Document $document, $action = null)
    {
        $this->document = $document;
        $this->action = $action;
        $this->user = auth()->user();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $privateChannels = [];

        foreach($this->document->users as $user) {
            $privateChannels[] = new PrivateChannel('events.' . $user->id);
        }

        foreach($this->document->mailingUsers()->wherePivotNull('read_at')->get() as $user) {
            // if ($user->id == $this->user->id) continue;

            $user->notify(new GeneralNotification([
                'title' => 'Новый документ',
                'message' => 'Ознакомьтесь с новым документом: '. $this->document->fullname,
                'link' => 'http://unisys-taskman.mkvityaz.ru/#/document/' . $this->document->id,
            ]));
        }
        
        return $privateChannels;
    }
}