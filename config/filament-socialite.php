<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OAuth callback middleware
    |--------------------------------------------------------------------------
    |
    | This option defines the middleware that is applied to the OAuth callback url.
    |
    */

    'providers' => [
        'google' => [
            'enabled' => true,
            'label' => 'Sign in with Google',
            'icon' => 'heroicon-o-globe-alt',
            'redirect_url' => env('GOOGLE_REDIRECT_URI'),
            'oauth' => 'google',
        ],
    ],

    'middleware' => [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ],
];
