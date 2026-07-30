<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Events\PasswordResetCompleted;
use App\Events\PasswordResetRequested;
use App\Events\PhoneVerificationRequested;
use App\Events\UserRegistered;
use App\Events\BackInStock;
use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderStatusNotification;
use App\Events\ProductCreated;
use App\Listeners\LogPasswordReset;
use App\Listeners\SendPasswordResetSms;
use App\Listeners\SendVerificationCodeSms;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\NotifyUsersAboutNewProduct;
use App\Listeners\NotifyBackInStockSubscribers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserRegistered::class => [
            SendWelcomeEmail::class,
        ],
        PhoneVerificationRequested::class => [
            SendVerificationCodeSms::class,
        ],
        PasswordResetRequested::class => [
            SendPasswordResetSms::class,
        ],
        PasswordResetCompleted::class => [
            LogPasswordReset::class,
            // Future: Send SMS notification, email notification
        ],
        ProductCreated::class => [
            NotifyUsersAboutNewProduct::class,
        ],
        BackInStock::class => [
            NotifyBackInStockSubscribers::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}