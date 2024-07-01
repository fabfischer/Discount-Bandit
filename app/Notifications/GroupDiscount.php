<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupDiscount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public  $group , public  $current_price , public $currency){}

    /**
     * Get the notification's delivery channels.
     *
     * @return string
     */
    public function via(object $notifiable): string
    {
        return NtfyChannel::class;
    }

    public function toNtfy($notifiable)
    {
        Ntfy::send(
            "Your Group '$this->group' Has Reached the Desired Price. $this->currency $this->current_price ",
            "Please refer to the website",
            "Please refer to the website"
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
