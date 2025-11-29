<?php

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [

        'smtp' => [
            'transport'    => 'smtp',
            'url'          => env('MAIL_URL'),
            'host'         => env('MAIL_HOST', 'smtp.gmail.com'),
            'port'         => env('MAIL_PORT', 587),
            'encryption'   => env('MAIL_ENCRYPTION', 'tls'),
            'username'     => env('MAIL_USERNAME'),
            'password'     => env('MAIL_PASSWORD'),
            'timeout'      => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),

            /*
            |--------------------------------------------------------------------------
            | FIX para Windows + XAMPP + PHP 8.2 + OpenSSL 3
            |--------------------------------------------------------------------------
            | Sin esto, SIEMPRE marca:
            | "SSL routines::certificate verify failed"
            |
            | Esto NO se usa en producción. Solo en LOCAL.
            */
            'verify_peer'      => false,
            'verify_peer_name' => false,
            'allow_self_signed'=> true,
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path'      => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers'   => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers'   => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name'    => env('MAIL_FROM_NAME', 'Example'),
    ],

];
