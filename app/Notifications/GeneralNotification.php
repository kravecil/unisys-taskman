<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    private $title;
    private $message;
    private $link;

    public function __construct($args = null)
    {
        $this->title =      $args['title']      ?? null;
        $this->message =    $args['message']    ?? null;
        $this->link =       $args['link']       ?? null;
    }

    public function via($notifiable)
    {
        return ['broadcast'];
    }

    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    public function toBroadcast($notifiable)
    {
        // var_dump($notifiable);

        return [
            // 'user_sender' => auth()->user() ?? null,
            // 'user_recipient' => $notifiable,
            'user' => auth()->user() ?? null,
            'datetime' => now()->format('d.m.Y H:i:s'),
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
        ];
    }
}
