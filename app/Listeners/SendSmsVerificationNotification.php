<?php

namespace App\Listeners;

use App\Events\SmsConfirmEvent;

class SendSmsVerificationNotification
{
    /**
     * Handle the event.
     */
    public function handle(SmsConfirmEvent $event): void
    {
        if ($event->user->hasVerifiedPhone()) {
            $event->user->sendPhoneVerificationNotification();
        }
    }
}
