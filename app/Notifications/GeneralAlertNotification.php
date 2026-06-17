<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Revolution\Line\Notifications\LineChannel;
use Revolution\Line\Notifications\LineMessage;

class GeneralAlertNotification extends Notification
{
    use Queueable;
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        $preferences = $notifiable->notification_preferences ?? [];
        $channels = [];

        if (in_array('email', $preferences) && $notifiable->hasVerifiedEmail()) {
            $channels[] = 'mail';
        }
        if (in_array('line', $preferences) && $notifiable->line_id) $channels[] = LineChannel::class;

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Notification from SoundScape')->line($this->message);
    }

    public function toLine(object $notifiable): LineMessage
    {
        return LineMessage::create()->text($this->message);
    }
}
