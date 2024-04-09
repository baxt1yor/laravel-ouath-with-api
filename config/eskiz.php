<?php

return [
    'auth' => [
        'email' => env('ESKIZ_SMS_EMAIL', 'admin@admin.com'),
        'password' => env('ESKIZ_SMS_PASSWORD', 'password'),
    ],

    'bast_url' => env('ESKIZ_URL', 'http://localhost'),

    'sms_from' => env('ESKIZ_FROM_NUMBER', 1111),
];
