<?php

namespace App\Listeners;

use App\Events\SurveyCreated;
use App\Notifications\SurveyCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSurveyCreatedNotification
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
    public function handle(SurveyCreated $event): void
    {
        if(auth()->user()) {
            auth()->user()->notify(new SurveyCreatedNotification($event->survey));
        }
    }
}
