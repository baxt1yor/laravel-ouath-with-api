<?php

namespace App\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class SmsConfirmEvent
{
    use SerializesModels;

    public function __construct(
        public Authenticatable|User $user
    ) {
    }
}
