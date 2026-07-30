<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductBackInStock extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The product that is back in stock.
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Back in Stock: ' . $this->product->title)
            ->greeting('Good news, ' . $notifiable->name . '!')
            ->line('The product you were waiting for is back in stock:')
            ->line('**' . $this->product->title . '**')
            ->line('Price: $' . number_format($this->product->price, 2))
            ->line('Quantity available: ' . $this->product->stock_quantity)
            ->action('View Product', url('/products/' . $this->product->id))
            ->line('Hurry up — stock is limited!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'stock_quantity' => $this->product->stock_quantity,
            'message' => $this->product->title . ' is back in stock!',
        ];
    }
}