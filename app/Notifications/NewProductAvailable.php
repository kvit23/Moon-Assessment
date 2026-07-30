<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProductAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The product instance.
     */
    protected Product $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // For assessment, we'll use database and mail
        // In production, you might add 'mail', 'sms', 'broadcast'
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Product Available!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We have a new product that might interest you:')
            ->line('**' . $this->product->title . '**')
            ->line('Price: $' . number_format($this->product->price, 2))
            ->when($this->product->description, function ($message) {
                return $message->line($this->product->description);
            })
            ->action('View Product', url('/products/' . $this->product->id))
            ->line('Thank you for being a valued customer!');
    }

    /**
     * Get the array representation of the notification.
     * Stored in the database notifications table.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'product_price' => $this->product->price,
            'message' => 'New product available: ' . $this->product->title,
        ];
    }
}