<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],
    'doku' => [
        'client_id' => env('DOKU_CLIENT_ID'),
        'secret_key' => env('DOKU_SECRET_KEY'),
        'sandbox' => env('DOKU_SANDBOX', true),
        'payment_due_date' => (int) env('DOKU_PAYMENT_DUE_DATE', 60),
    ],
    'admin_emails' => array_filter(array_map(fn ($e) => strtolower(trim($e)), explode(',', (string) env('ADMIN_EMAILS', '')))),
    // Fase 1: kunci pendaftaran — REGISTRATION_MODE=open|domain|invite (default open agar demo tidak rusak)
    'registration' => env('REGISTRATION_MODE', 'open'),
    'allowed_domains' => array_filter(array_map(fn ($e) => strtolower(trim($e)), explode(',', (string) env('ALLOWED_DOMAINS', '')))),
];
