<?php

namespace App\Http\Controllers;

use App\DTO\AppResponse;
use App\OAUTH\AuthorizationServer;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Exceptions\OAuthServerException as OAuthServerExceptionAlias;
use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\Http\Controllers\RetrievesAuthRequestFromSession;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;

final class AuthorizationController extends Controller
{
    use ConvertsPsrResponses, HandlesOAuthErrors, RetrievesAuthRequestFromSession;
    use HandlesOAuthErrors;

    public function __construct(
        protected AuthorizationServer $server,
        protected Guard $guard
    ) {
    }

    /**
     * @return JsonResponse|Response
     *
     * @throws AuthenticationException
     * @throws OAuthServerException|OAuthServerExceptionAlias
     */
    public function authorize(ServerRequestInterface $psrRequest,
        Request $request,
        ClientRepository $clients,
        TokenRepository $tokens)
    {
        $authRequest = $this->withErrorHandling(function () use ($psrRequest) {
            return $this->server->validateAuthorizationRequest($psrRequest);
        });

        if ($this->guard->guest()) {
            return $this->promptForLogin($request);
        }

        $scopes = $this->parseScopes($authRequest);
        $user = $this->guard->user();
        $client = $clients->find($authRequest->getClient()->getIdentifier());

        if ($request->get('prompt') !== 'consent' &&
            ($client->skipsAuthorization() || $this->hasValidToken($tokens, $user, $client, $scopes))) {
            return $this->approveRequest($authRequest, $user);
        }

        if ($request->get('prompt') === 'none') {
            return $this->denyRequest($authRequest, $user);
        }

        $request->session()->put('authToken', $authToken = Str::random());
        $request->session()->put('authRequest', $authRequest);

        return AppResponse::success([
            'client' => [
                'name' => $client->name,
                'client_id' => $client->getKey(),
            ],
            'scopes' => $scopes,
            'state' => $request->state,
            'authToken' => $authToken,
        ]);
    }

    /**
     * Transform the authorization requests's scopes into Scope instances.
     *
     * @return array
     */
    protected function parseScopes(AuthorizationRequest $authRequest)
    {
        return Passport::scopesFor(
            collect($authRequest->getScopes())->map(function ($scope) {
                return $scope->getIdentifier();
            })->unique()->all()
        );
    }

    /**
     * Determine if a valid token exists for the given user, client, and scopes.
     *
     * @return bool
     */
    protected function hasValidToken(TokenRepository $tokens, Authenticatable $user, Client $client, array $scopes)
    {
        return $tokens->findValidToken($user, $client)?->scopes === collect($scopes)->pluck('id')->all();
    }

    /**
     * @return mixed
     *
     * @throws OAuthServerExceptionAlias
     */
    protected function approveRequest(AuthorizationRequest $authRequest, Authenticatable $user)
    {
        $authRequest->setUser(new User($user->getAuthIdentifier()));

        $authRequest->setAuthorizationApproved(true);

        return $this->withErrorHandling(function () use ($authRequest) {
            return AppResponse::success(['redirect_uri' => $this->server->getGrantType($authRequest)->completeAuthorizationRequest($authRequest)]);
        });
    }

    /**
     * @return mixed
     *
     * @throws OAuthServerExceptionAlias
     */
    protected function denyRequest(AuthorizationRequest $authRequest, ?Authenticatable $user = null)
    {
        if (is_null($user)) {
            $uri = $authRequest->getRedirectUri()
                ?? (is_array($authRequest->getClient()->getRedirectUri())
                    ? $authRequest->getClient()->getRedirectUri()[0]
                    : $authRequest->getClient()->getRedirectUri());

            $separator = $authRequest->getGrantTypeId() === 'implicit' ? '#' : '?';

            $uri = $uri.(str_contains($uri, $separator) ? '&' : $separator).'state='.$authRequest->getState();

            return $this->withErrorHandling(function () use ($uri) {
                throw OAuthServerException::accessDenied('Unauthenticated', $uri);
            });
        }

        $authRequest->setUser(new User($user->getAuthIdentifier()));

        $authRequest->setAuthorizationApproved(false);

        return $this->withErrorHandling(function () use ($authRequest) {
            return $this->convertResponse(
                $this->server->completeAuthorizationRequest($authRequest, new Psr7Response)
            );
        });
    }

    /**
     * Prompt the user to login by throwing an AuthenticationException.
     *
     *
     * @throws AuthenticationException
     */
    protected function promptForLogin(Request $request)
    {
        $request->session()->put('promptedForLogin', true);

        throw new AuthenticationException;
    }

    /**
     * @throws OAuthServerExceptionAlias
     * @throws Exception
     */
    public function approve(Request $request)
    {
        $this->assertValidAuthToken($request);

        $authRequest = $this->getAuthRequestFromSession($request);

        $authRequest->setAuthorizationApproved(true);

        return $this->withErrorHandling(function () use ($authRequest) {
            return AppResponse::success(['redirect_uri' => $this->server->getGrantType($authRequest)->completeAuthorizationRequest($authRequest)]);
        });
    }

    public function issueToken(ServerRequestInterface $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            return $this->convertResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response)
            );
        });
    }
}
