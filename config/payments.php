<?php

return [

    // Devise par défaut (Burkina Faso = XOF, sans décimales)
    'currency' => env('PAYMENTS_CURRENCY', 'XOF'),

    // URL publique de base pour les callbacks/webhooks (tunnel type ngrok en local).
    // Laissé vide => on retombe sur app.url. Les webhooks n'arrivent pas sur localhost :
    // le POS utilise le polling de statut comme source de vérité.
    'callback_url' => env('PAYMENTS_CALLBACK_URL', env('APP_URL')),

    'providers' => [

        'wave' => [
            'enabled'        => env('WAVE_ENABLED', false),
            'base_url'       => env('WAVE_BASE_URL', 'https://api.wave.com'),
            'api_key'        => env('WAVE_API_KEY'),
            'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
        ],

        'orange' => [
            'enabled'       => env('ORANGE_ENABLED', false),
            'base_url'      => env('ORANGE_BASE_URL', 'https://api.orange.com'),
            'client_id'     => env('ORANGE_CLIENT_ID'),
            'client_secret' => env('ORANGE_CLIENT_SECRET'),
            'merchant_key'  => env('ORANGE_MERCHANT_KEY'),
            'country'       => env('ORANGE_COUNTRY', 'bf'),   // segment URL Web Payment
            'currency'      => env('ORANGE_CURRENCY', 'XOF'), // 'OUV' en sandbox Orange
            'lang'          => env('ORANGE_LANG', 'fr'),
        ],

        'moov' => [
            'enabled'       => env('MOOV_ENABLED', false),
            'base_url'      => env('MOOV_BASE_URL'),          // ex: https://api.moov-africa.bf
            'token_path'    => env('MOOV_TOKEN_PATH', '/token'),
            'request_path'  => env('MOOV_REQUEST_PATH', '/payment/requesttopay'),
            'status_path'   => env('MOOV_STATUS_PATH', '/payment/status'),
            'username'      => env('MOOV_USERNAME'),
            'password'      => env('MOOV_PASSWORD'),
            'merchant_code' => env('MOOV_MERCHANT_CODE'),
        ],
    ],
];
