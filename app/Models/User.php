<?php

namespace App\Models;

use App\Notifications\SendTwoFactorCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use libphonenumber\PhoneNumberFormat;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $phone_number
 * @property mixed|string $password
 * @property int $id
 * @property int|null $two_factor_code
 * @property Carbon|null $two_factor_expires_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'avatar',
        'password',
    ];

    public function phoneNumber(): Attribute
    {
        return new Attribute(
            get: fn (string $value, array $attribute) => str(phone($attribute['phone_number'], ['UZ'], PhoneNumberFormat::E164))->replaceFirst('+', '')->value(),
            set: fn (string $value) => ['phone_number' => $value]
        );
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_code' => 'integer',
        'two_factor_expires_at' => 'datetime',
    ];

    public function routeNotificationForEskiz(): string
    {
        return $this->phone_number;
    }

    public function hasVerifiedPhone(): bool
    {
        return ! isset($this->two_factor_expires_at);
    }

    public function passwordCheck(string $value): bool
    {
        return Hash::check($value, $this->password);
    }

    public function sendPhoneVerificationNotification(): void
    {
        $this->notify(new SendTwoFactorCode());
    }

    public function generateTwoFactorCode(): void
    {
        $this->timestamps = false;
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes();
        $this->save();
    }

    public function resetTwoFactorCode(): void
    {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }
}
