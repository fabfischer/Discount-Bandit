<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Webhook\WebhookChannel;
use NotificationChannels\Webhook\WebhookMessage;

class ProductDiscount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $product_name,
        public $store_name,
        public $price,
        public $product_url,
        public $image,
        public $currency
    ) {
    }


    /**
     * Get the notification channels.
     */
    public function via(object $notifiable): string
    {
        return NtfyChannel::class;
    }

    public function toNtfy($notifiable)
    {
        Ntfy::send(
            "$this->price$this->currency - " . \Str::words($this->product_name, 5),
            "view, Open in $this->store_name, $this->product_url",
            "Your product $this->product_name, is at discount with price $this->currency $this->price",
            $this->image
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
