<?php

return [
    'allowed_hosts' => array_values(array_filter(array_map(
        static fn (string $host) => strtolower(trim($host)),
        explode(',', env('WORKFLOW_SOURCE_HOSTS', env('INTEGRATION_FEED_HOSTS', '')))
    ))),
    'http_timeout' => max(5, (int) env('WORKFLOW_HTTP_TIMEOUT', 30)),
    'max_response_bytes' => max(1024, (int) env('WORKFLOW_MAX_RESPONSE_BYTES', 5_000_000)),
    'max_records_per_run' => max(1, (int) env('WORKFLOW_MAX_RECORDS_PER_RUN', 5_000)),
];
