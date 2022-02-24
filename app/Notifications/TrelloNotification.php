<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Trello\TrelloChannel;
use NotificationChannels\Trello\TrelloMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class TrelloNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $arr;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $arr)
    {
        $this->arr = $arr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TrelloChannel::class, 'mail'];
    }

    public function toTrello($notifiable)
    {
        return TrelloMessage::create()
            ->name($this->arr['name'])
            ->description($this->arr['description'])
            ->top()
            ->due('now');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Nieuwe error @ MijnStalling')
            ->line('Nieuwe error in Trello');
    }

}
