<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserRegistration
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification());
        Log::info('Yeni kullanıcı kaydı yapıldı.', [
            'id' => $event->user->id,
            'email' => $event->user->email,
            'time' => now()->toDateTimeString(),
        ]);
    }
}
