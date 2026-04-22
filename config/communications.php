<?php

$appUrl = rtrim((string) env('APP_URL', 'http://localhost:8000'), '/');
$primaryPhone = (string) env('SITE_PHONE_E164', '+359878369024');
$phoneDigits = preg_replace('/\D+/', '', $primaryPhone);
$facebookUsername = (string) env('FACEBOOK_PAGE_USERNAME', 'JAR.bg');
$supportEmail = (string) env('SITE_SUPPORT_EMAIL', 'office_bl@jarcomputers.com');
$redirectHosts = array_values(array_filter(array_map('trim', explode(',', (string) env('REDIRECT_HOSTS', '')))));

return [
    'domain' => [
        'primary' => env('PRIMARY_DOMAIN'),
        'canonical_host' => env('CANONICAL_HOST'),
        'redirect_hosts' => $redirectHosts,
        'force_https' => env('FORCE_HTTPS', false),
        'asset_url' => env('ASSET_URL'),
        'app_url' => $appUrl,
    ],

    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS', $supportEmail),
        'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'JAR Computers Благоевград')),
        'reply_to_address' => env('MAIL_REPLY_TO_ADDRESS', $supportEmail),
        'reply_to_name' => env('MAIL_REPLY_TO_NAME', env('APP_NAME', 'JAR Computers Благоевград')),
        'notifications_to' => array_values(array_filter(array_map('trim', explode(',', (string) env('CONTACT_NOTIFICATION_EMAILS', env('CONTACT_NOTIFICATION_EMAIL', $supportEmail)))))),
    ],

    'channels' => [
        'email' => [
            'enabled' => true,
            'label' => 'Email',
        ],
        'whatsapp' => [
            'enabled' => env('WHATSAPP_ENABLED', false),
            'label' => 'WhatsApp',
            'phone' => $primaryPhone,
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
            'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
            'app_secret' => env('WHATSAPP_APP_SECRET', env('FACEBOOK_APP_SECRET')),
            'webhook_url' => $appUrl.'/webhooks/whatsapp',
            'public_url' => env('WHATSAPP_PUBLIC_URL', 'https://wa.me/'.$phoneDigits),
        ],
        'facebook-messenger' => [
            'enabled' => env('FACEBOOK_MESSENGER_ENABLED', false),
            'label' => 'Facebook Messenger',
            'page_id' => env('FACEBOOK_PAGE_ID'),
            'page_username' => $facebookUsername,
            'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
            'verify_token' => env('FACEBOOK_VERIFY_TOKEN'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'webhook_url' => $appUrl.'/webhooks/facebook-messenger',
            'public_url' => env('FACEBOOK_MESSENGER_PUBLIC_URL', 'https://m.me/'.$facebookUsername),
        ],
        'viber' => [
            'enabled' => env('VIBER_ENABLED', false),
            'label' => 'Viber',
            'bot_name' => env('VIBER_BOT_NAME', 'JAR Computers'),
            'bot_token' => env('VIBER_BOT_TOKEN'),
            'webhook_secret' => env('VIBER_WEBHOOK_SECRET'),
            'webhook_url' => $appUrl.'/webhooks/viber',
            'public_url' => env('VIBER_PUBLIC_URL', 'viber://chat?number='.rawurlencode($primaryPhone)),
        ],
    ],
];
