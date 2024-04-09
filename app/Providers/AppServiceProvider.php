<?php

namespace App\Providers;

use App\Channels\EskizSmsChannel;
use App\Events\SmsConfirmEvent;
use App\Exceptions\Handler;
use App\Listeners\SendSmsVerificationNotification;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use SocialiteProviders\Facebook\FacebookExtendSocialite;
use SocialiteProviders\GitHub\GitHubExtendSocialite;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Telegram\TelegramExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passport::ignoreCsrfToken();
        Passport::ignoreRoutes();
    }

    public function boot(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
        Event::listen(SmsConfirmEvent::class, SendSmsVerificationNotification::class);

        Event::listen(SocialiteWasCalled::class, FacebookExtendSocialite::class);
        Event::listen(SocialiteWasCalled::class, GoogleExtendSocialite::class);
        Event::listen(SocialiteWasCalled::class, GitHubExtendSocialite::class);
        Event::listen(SocialiteWasCalled::class, TelegramExtendSocialite::class);

        Notification::resolved(fn (ChannelManager $manager) => $manager->extend('eskiz', fn () => EskizSmsChannel::instance()));

        Passport::tokensExpireIn(now()->addDays(10));
        Passport::refreshTokensExpireIn(now()->addDays(10));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::setDefaultScope([
            'create',
            'place-orders',
        ]);
    }
}
