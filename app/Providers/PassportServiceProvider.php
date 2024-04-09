<?php

namespace App\Providers;

use App\OAUTH\AuthCodeGrant;
use App\OAUTH\AuthorizationServer;
use DateInterval;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\AuthCodeRepository;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\PersonalAccessGrant;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\ScopeRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Override;

class PassportServiceProvider extends \Laravel\Passport\PassportServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function makeAuthorizationServer(): AuthorizationServer
    {
        return new AuthorizationServer(
            $this->app->make(ClientRepository::class),
            $this->app->make(AccessTokenRepository::class),
            $this->app->make(ScopeRepository::class),
            $this->makeCryptKey('private'),
            app('encrypter')->getKey(),
            Passport::$authorizationServerResponseType
        );
    }

    #[Override]
    protected function registerAuthorizationServer(): void
    {
        $this->app->singleton(AuthorizationServer::class, function () {
            return tap($this->makeAuthorizationServer(), function ($server) {
                $server->setDefaultScope(Passport::$defaultScope);

                $server->enableGrantType(
                    $this->makeAuthCodeGrant(), Passport::tokensExpireIn()
                );

                $server->enableGrantType(
                    $this->makeRefreshTokenGrant(), Passport::tokensExpireIn()
                );

                if (Passport::$passwordGrantEnabled) {
                    $server->enableGrantType(
                        $this->makePasswordGrant(), Passport::tokensExpireIn()
                    );
                }

                $server->enableGrantType(
                    new PersonalAccessGrant, Passport::personalAccessTokensExpireIn()
                );

                $server->enableGrantType(
                    new ClientCredentialsGrant, Passport::tokensExpireIn()
                );

                if (Passport::$implicitGrantEnabled) {
                    $server->enableGrantType(
                        $this->makeImplicitGrant(), Passport::tokensExpireIn()
                    );
                }
            });
        });
    }

    /**
     * Build the Auth Code grant instance.
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    #[Override]
    protected function buildAuthCodeGrant(): AuthCodeGrant
    {
        return new AuthCodeGrant(
            $this->app->make(AuthCodeRepository::class),
            $this->app->make(RefreshTokenRepository::class),
            new DateInterval('PT10M')
        );
    }
}
