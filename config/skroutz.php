<?php

return [
    'feed_enabled' => (bool) env('SKROUTZ_FEED_ENABLED', false),
    // Official ranges: https://developer.skroutz.gr/smart_cart/orders_webhook/ and /products/xml_feed/
    'webhook_allowed_ips' => array_filter(explode(',', env('SKROUTZ_WEBHOOK_ALLOWED_IPS', env('SKROUTZ_ALLOWED_IPS', '185.6.76.0/22,3.73.204.153/32,3.72.204.195/32,3.67.183.221/32,63.34.193.172/32,54.195.53.34/32,108.129.50.199/32,2a03:e40::/32')))),
    'feed_allowed_ips' => array_filter(explode(',', env('SKROUTZ_FEED_ALLOWED_IPS', env('SKROUTZ_ALLOWED_IPS', '185.6.76.0/22,3.73.204.153/32,3.72.204.195/32,3.67.183.221/32,63.34.193.172/32,54.195.53.34/32,108.129.50.199/32,2a03:e40::/32')))),
];
