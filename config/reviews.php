<?php

return [
    'aggregate' => [
        'label' => 'Обща оценка',
        'rating_value' => 8.6,
        'rating_scale' => 10,
        'reviews_count' => 147,
        'source_label' => 'Орли Електроника',
        'source_url' => 'https://www.orlielektronika.eu/profile-7626-jar-computers',
        'scan_date' => '2025-08-19',
    ],

    'platforms' => [
        [
            'key' => 'google-maps',
            'label' => 'Google Maps',
            'rating_value' => 4.7,
            'rating_scale' => 5,
            'reviews_count' => 62,
            'primary' => true,
            'scan_date' => '2025-08-19',
            'source_url' => env('SITE_GOOGLE_REVIEWS_URL', 'https://www.google.com/search?sa=X&sca_esv=ef5fb46a2a3e8e1b&sxsrf=ANbL-n6a3tyEYP-mr3-ZbL1QmkmP_VeihQ:1776200303692&q=JAR+Computers+Reviews&rflfq=1&num=20&stick=H4sIAAAAAAAAAONgkxI2MjE1Mrc0sTA3tTCyNDK2sDQy2cDI-IpR1MsxSME5P7egtCS1qFghKLUsM7W8eBErdnEA6OvF30oAAAA&rldimm=2452794875829238924&tbm=lcl&hl=en-BG&ved=2ahUKEwjJ7prhne6TAxVpBNsEHfW4GnEQ9fQKegQISRAG&biw=1920&bih=951&dpr=1#lkt=LocalPoiReviews'),
            'snapshot_url' => 'https://www.orlielektronika.eu/profile-7626-jar-computers',
        ],
        [
            'key' => 'facebook',
            'label' => 'Facebook',
            'rating_value' => 4.0,
            'rating_scale' => 5,
            'reviews_count' => 85,
            'primary' => false,
            'scan_date' => '2025-08-19',
            'source_url' => env('FACEBOOK_PAGE_URL', 'https://bg-bg.facebook.com/JAR.bg/'),
            'snapshot_url' => 'https://www.orlielektronika.eu/profile-7626-jar-computers',
        ],
    ],
];
