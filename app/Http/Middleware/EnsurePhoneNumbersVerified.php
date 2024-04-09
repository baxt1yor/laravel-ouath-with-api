<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneNumbersVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasVerifiedPhone()) {
            return response()->json(['message' => 'Your email address is not verified.'], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
