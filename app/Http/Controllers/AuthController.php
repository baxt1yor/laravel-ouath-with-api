<?php

namespace App\Http\Controllers;

use App\DTO\AppResponse;
use App\Events\SmsConfirmEvent;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendRequest;
use App\Http\Requests\VerifyRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function login(LoginRequest $request)
    {
        /** @var User $user */
        $user = User::query()->where('phone_number', $request->phone)->first();
        if (! $user->passwordCheck($request->password)) {
            return AppResponse::error(['message' => __('auth.password')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return AppResponse::success(['token' => $user->createToken($user->id)->accessToken]);
    }

    /**
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone_number = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        $user->generateTwoFactorCode();

        event(new SmsConfirmEvent($user));

        return AppResponse::success([
            'message' => 'Biz sizning quyidagi '.$user->phone_number." ko'rsatilgan telefon raqamiga tasqdilash kodini jo'natdik",
        ]);
    }

    public function resend(ResendRequest $request)
    {
        /** @var User $user */
        $user = User::query()->where('phone_number', $request->phone)->first();
        if ($user instanceof User) {
            $user->generateTwoFactorCode();
            event(new SmsConfirmEvent($user));

            return AppResponse::success([
                'message' => 'Biz sizning quyidagi '.$user->phone_number." ko'rsatilgan telefon raqamiga tasqdilash kodini jo'natdik",
            ]);
        }

        return AppResponse::error([
            'message' => __('No found user'),
        ], Response::HTTP_NOT_FOUND);
    }

    public function verify(VerifyRequest $request)
    {
        /** @var User $user */
        $user = User::query()->where('phone_number', $request->phone)->whereNotNull('two_factor_code')->first();
        if ($request->code === $user?->two_factor_code) {
            $user->resetTwoFactorCode();

            return AppResponse::success(['token' => $user->createToken($user->id)->accessToken]);
        }

        return AppResponse::error(['message' => 'Verify code expired'], Response::HTTP_GONE);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return AppResponse::success(['message' => 'Successfully logged out']);
    }
}
