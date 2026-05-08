<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vkontakte' => [
        'client_id' => env('VKONTAKTE_CLIENT_ID'),
        'client_secret' => env('VKONTAKTE_CLIENT_SECRET'),
        'redirect' => env('VKONTAKTE_REDIRECT_URI'),
        /*
         | Домен для синтетического email, если VK не вернёт почту (локальное поле users.email).
         | Настоящие письма на этот домен не отправляются.
         */
        'placeholder_email_domain' => env('VK_PLACEHOLDER_EMAIL_DOMAIN', 'oauth.local'),
        'lang' => env('VK_LANG', 'ru'),
        /** Права для VK ID виджета (через пробел): например vkid.personal_info email */
        'id_scope' => env('VK_ID_SCOPE', 'vkid.personal_info email'),
        /** URL UMD сборки SDK (при необходимости зафиксировать версию) */
        'id_sdk_script' => env('VK_ID_SDK_SCRIPT', 'https://unpkg.com/@vkid/sdk@2/dist-sdk/umd/index.js'),
    ],

];
