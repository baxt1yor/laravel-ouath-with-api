<?php

namespace App\Channels;

use App\Messages\EskizMessage;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class EskizSmsChannel
{
    protected static ?self $instance = null;

    protected string $email;

    protected string $password;

    protected string $base_url;

    protected int $from;

    protected string $token;

    public function __construct()
    {
        $this->email = (string) config('eskiz.auth.email');
        $this->password = (string) config('eskiz.auth.password');
        $this->base_url = (string) config('eskiz.bast_url');
        $this->from = (int) config('eskiz.sms_from');
        $this->token = (string) $this->auth();
    }

    protected function auth(): ?string
    {
        return Http::asForm()->post($this->base_url.'/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
        ])->object()?->data?->token;
    }

    public static function instance(): static
    {
        return static::$instance ??= new static();
    }

    /**
     * Send the given notification.
     *
     * @param  User  $notifiable
     */
    public function send($notifiable, Notification $notification): bool
    {
        if (! $to = $notifiable->routeNotificationFor('eskiz', $notification)) {
            return false;
        }

        $message = $notification->toEskiz($notifiable);

        if (is_string($message)) {
            $message = new EskizMessage($message);
        }

        return Http::asForm()->withToken($this->token)->post($this->base_url.'/message/sms/send', [
            'mobile_phone' => $to,
            'message' => trim($message->content),
            'from' => $message->from ?? $this->from,
            'callback_url' => $message->statusCallback,
        ])->ok();
    }
}
