<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Add this entry
        \App\Events\MessageSent::class => [
           

        ],
    ];

    public function boot()
    {
        parent::boot();
    }

    public function shouldDiscoverEvents()
    {
        return false; // Disable discovery for now
    }
}