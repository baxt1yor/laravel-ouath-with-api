<?php

namespace App\OAUTH;

use League\OAuth2\Server\AuthorizationServer as BaseAuthorizationServer;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class AuthorizationServer extends BaseAuthorizationServer
{
    public function getGrantType(AuthorizationRequest $authRequest): GrantTypeInterface
    {
        return $this->enabledGrantTypes[$authRequest->getGrantTypeId()];
    }
}
