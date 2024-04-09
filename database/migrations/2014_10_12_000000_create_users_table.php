<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->date('birth_date')->nullable();
            $table->string('avatar')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('two_factor_code')->nullable();
            $table->dateTime('two_factor_expires_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unique(['email', 'phone_number'], 'users_email_phone_number_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
