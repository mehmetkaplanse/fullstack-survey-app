<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\UserRegistered::class => [
            \App\Listeners\LogUserRegistration::class,
        ],
        \App\Events\SurveyCreated::class => [
            \App\Listeners\SendSurveyCreatedNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
