<?php

return [
    'polling_floor_minutes' => max(1, (int) env('INTEGRATION_POLLING_FLOOR_MINUTES', 1)),
    // A server-owned allowlist keeps an admin-supplied feed URL from targeting arbitrary services.
    'feed_hosts' => array_values(array_filter(array_map('trim', explode(',', env('INTEGRATION_FEED_HOSTS', ''))))),
    'http_timeout' => 30,
    'ca_bundle' => env('INTEGRATION_CA_BUNDLE'),
    'max_pages' => 1000,
    'benchmark_max_age_days' => 7,
    'bgn_per_eur' => 1.95583,
];
