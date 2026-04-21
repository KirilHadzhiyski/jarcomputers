<?php

$primaryPhone = env('SITE_PHONE_E164', '+359878369024');
$landlinePhone = env('SITE_LANDLINE_E164', '+35973831212');
$publicEmail = env('SITE_PUBLIC_EMAIL', 'blagoevgrad@jarcomputers.com');
$supportEmail = env('SITE_SUPPORT_EMAIL', 'office@jarcomputers.com');
$facebookPageUsername = env('FACEBOOK_PAGE_USERNAME', 'JAR.bg');

return [
    'brand' => env('SITE_BRAND', 'JAR Computers Благоевград'),
    'company_name' => env('SITE_COMPANY_NAME', 'JAR Computers'),
    'phone' => env('SITE_PHONE_DISPLAY', '0878 369 024'),
    'phone_e164' => $primaryPhone,
    'phone_href' => preg_replace('/\D+/', '', $primaryPhone),
    'landline' => env('SITE_LANDLINE_DISPLAY', '073 831 212'),
    'landline_e164' => $landlinePhone,
    'landline_href' => preg_replace('/\D+/', '', $landlinePhone),
    'email' => $publicEmail,
    'support_email' => $supportEmail,
    'notification_email' => env('CONTACT_NOTIFICATION_EMAIL', $supportEmail),
    'address' => env('SITE_ADDRESS', 'бул. „Джеймс Баучер“ 10, Благоевград 2700'),
    'short_address' => env('SITE_SHORT_ADDRESS', 'бул. „Джеймс Баучер“ 10'),
    'city_name' => env('SITE_CITY_NAME', 'Благоевград'),
    'postal_code' => env('SITE_POSTAL_CODE', '2700'),
    'google_maps_url' => env('SITE_GOOGLE_MAPS_URL', 'https://www.google.com/maps/search/?api=1&query=42.0161815,23.0954484'),
    'coordinates' => [
        'lat' => (float) env('SITE_LATITUDE', 42.0161815),
        'lng' => (float) env('SITE_LONGITUDE', 23.0954484),
    ],
    'hours' => [
        'Понеделник – Петък: 09:00 – 19:00',
        'Събота: 10:00 – 15:00',
        'Неделя: Почивен ден',
    ],
    'socials' => [
        [
            'label' => 'Facebook',
            'href' => env('FACEBOOK_PAGE_URL', 'https://bg-bg.facebook.com/JAR.bg/'),
        ],
        [
            'label' => 'Instagram',
            'href' => env('INSTAGRAM_URL', 'https://www.instagram.com/jarcomputers/'),
        ],
        [
            'label' => 'TikTok',
            'href' => env('TIKTOK_URL', 'https://www.tiktok.com/@jar_computers'),
        ],
    ],
    'messaging_channels' => [
        [
            'key' => 'whatsapp',
            'label' => 'WhatsApp',
            'href' => env('WHATSAPP_PUBLIC_URL', 'https://wa.me/'.preg_replace('/\D+/', '', $primaryPhone)),
        ],
        [
            'key' => 'viber',
            'label' => 'Viber',
            'href' => env('VIBER_PUBLIC_URL', 'viber://chat?number='.rawurlencode($primaryPhone)),
        ],
        [
            'key' => 'facebook-messenger',
            'label' => 'Messenger',
            'href' => env('FACEBOOK_MESSENGER_PUBLIC_URL', 'https://m.me/'.$facebookPageUsername),
        ],
    ],
    'legal' => [
        'privacy_label' => 'Политика за поверителност',
        'privacy_href' => '/politika-za-poveritelnost',
        'terms_label' => 'Общи условия',
        'terms_href' => '/obshti-usloviya',
    ],
    'navigation' => [
        ['label' => 'Начало', 'href' => '/'],
        ['label' => 'Услуги', 'href' => '/remont-iphone'],
        ['label' => 'Модели', 'href' => '/remont-iphone-11'],
        ['label' => 'Градове', 'href' => '/remont-iphone-sofia'],
        ['label' => 'Цени', 'href' => '/ceni'],
        ['label' => 'За нас', 'href' => '/za-nas'],
        ['label' => 'ЧЗВ', 'href' => '/chzv'],
        ['label' => 'Контакти', 'href' => '/kontakti'],
    ],
    'services' => [
        [
            'slug' => 'smqna-displei-iphone',
            'name' => 'Смяна на дисплей',
            'short_name' => 'Дисплей',
            'description' => 'Професионална смяна на оригинален и съвместим дисплей за всички модели iPhone.',
            'price_from' => 89,
            'keywords' => 'смяна дисплей iphone, смяна стъкло iphone',
            'badge' => 'SD',
        ],
        [
            'slug' => 'smqna-bateria-iphone',
            'name' => 'Смяна на батерия',
            'short_name' => 'Батерия',
            'description' => 'Бърза смяна на батерия с качествени части и до 12 месеца гаранция.',
            'price_from' => 49,
            'keywords' => 'смяна батерия iphone, батерия iphone',
            'badge' => 'SB',
        ],
        [
            'slug' => 'remont-face-id-iphone',
            'name' => 'Ремонт Face ID',
            'short_name' => 'Face ID',
            'description' => 'Специализиран ремонт на Face ID модула за възстановяване на функционалността.',
            'price_from' => 119,
            'keywords' => 'ремонт face id iphone, face id не работи',
            'badge' => 'FI',
        ],
        [
            'slug' => 'remont-kamera-iphone',
            'name' => 'Ремонт камера',
            'short_name' => 'Камера',
            'description' => 'Ремонт и смяна на предна и задна камера за всички модели iPhone.',
            'price_from' => 69,
            'keywords' => 'ремонт камера iphone, смяна камера iphone',
            'badge' => 'RK',
        ],
    ],
    'models' => [
        ['slug' => 'remont-iphone-11', 'name' => 'iPhone 11', 'series' => '11', 'image' => 'images/models/iphone-11.svg', 'accent' => '#7c3aed'],
        ['slug' => 'remont-iphone-12', 'name' => 'iPhone 12', 'series' => '12', 'image' => 'images/models/iphone-12.svg', 'accent' => '#2563eb'],
        ['slug' => 'remont-iphone-13', 'name' => 'iPhone 13', 'series' => '13', 'image' => 'images/models/iphone-13.svg', 'accent' => '#14b8a6'],
        ['slug' => 'remont-iphone-14', 'name' => 'iPhone 14', 'series' => '14', 'image' => 'images/models/iphone-14.svg', 'accent' => '#f97316'],
        ['slug' => 'remont-iphone-15', 'name' => 'iPhone 15', 'series' => '15', 'image' => 'images/models/iphone-15.svg', 'accent' => '#ec4899'],
        ['slug' => 'remont-iphone-16', 'name' => 'iPhone 16', 'series' => '16', 'image' => 'images/models/iphone-16.svg', 'accent' => '#0ea5e9'],
    ],
    'cities' => [
        ['slug' => 'remont-iphone-sofia', 'name' => 'София', 'name_en' => 'Sofia'],
        ['slug' => 'remont-iphone-plovdiv', 'name' => 'Пловдив', 'name_en' => 'Plovdiv'],
        ['slug' => 'remont-iphone-varna', 'name' => 'Варна', 'name_en' => 'Varna'],
        ['slug' => 'remont-iphone-burgas', 'name' => 'Бургас', 'name_en' => 'Burgas'],
    ],
    'steps' => [
        ['num' => 1, 'title' => 'Поръчваш онлайн', 'desc' => 'Попълваш кратка форма с модел и проблем.'],
        ['num' => 2, 'title' => 'Взимаме телефона с куриер', 'desc' => 'Куриер идва до теб безплатно в двете посоки.'],
        ['num' => 3, 'title' => 'Диагностицираме', 'desc' => 'Проверяваме устройството и потвърждаваме цената.'],
        ['num' => 4, 'title' => 'Ремонтираме', 'desc' => 'Извършваме ремонта в рамките на 24–48 часа.'],
        ['num' => 5, 'title' => 'Връщаме устройството', 'desc' => 'Получаваш телефона си ремонтиран с гаранция.'],
    ],
    'trust_items' => [
        ['text' => 'Реални отзиви', 'href' => '/#reviews'],
        ['text' => 'Гаранция до 12 мес.', 'href' => '/#warranty'],
        ['text' => 'Куриер в двете посоки', 'href' => '/#courier'],
        ['text' => 'Експресен ремонт 24-48ч', 'href' => '/#express-service'],
    ],
    'why_us' => [
        ['title' => 'Над 10 години опит', 'desc' => 'Доверие, изградено с хиляди успешни ремонти.'],
        ['title' => '5000+ ремонтирани устройства', 'desc' => 'Доказан опит с всички модели iPhone.'],
        ['title' => 'Качествени части', 'desc' => 'Използваме само проверени и тествани компоненти.'],
        ['title' => 'Ясно ценообразуване', 'desc' => 'Без скрити такси – знаеш цената предварително.'],
        ['title' => 'Реални снимки и ревюта', 'desc' => 'Вижте реални резултати от нашата работа.'],
        ['title' => 'Бърза комуникация', 'desc' => 'Отговаряме бързо на всяко запитване.'],
    ],
    'faq_home' => [
        [
            'q' => 'Как работи куриерската услуга?',
            'a' => 'Поръчвате ремонт онлайн, изпращаме куриер до вашия адрес, ремонтираме устройството и го връщаме – всичко без да излизате от дома.',
        ],
        [
            'q' => 'Колко време отнема ремонтът?',
            'a' => 'Повечето ремонти се извършват в рамките на 24–48 часа след получаване на устройството.',
        ],
        [
            'q' => 'Какви части използвате?',
            'a' => 'Използваме качествени съвместими и оригинални части с гаранция до 12 месеца.',
        ],
        [
            'q' => 'Трябва ли да плащам, ако не одобря ремонта?',
            'a' => 'Не. Диагностиката е безплатна и плащате само ако одобрите предложената цена.',
        ],
        [
            'q' => 'Обслужвате ли цяла България?',
            'a' => 'Да, предлагаме куриерска услуга в двете посоки за цяла България.',
        ],
        [
            'q' => 'Какво покрива гаранцията?',
            'a' => 'Гаранцията покрива дефекти в използваните части и извършената работа за срок до 12 месеца.',
        ],
    ],
    'faq_extra' => [
        [
            'q' => 'Какви модели iPhone ремонтирате?',
            'a' => 'Ремонтираме всички модели iPhone – от iPhone 6 до най-новите модели. Специализирани сме в iPhone 11, 12, 13, 14, 15 и 16.',
        ],
        [
            'q' => 'Мога ли да следя статуса на ремонта?',
            'a' => 'Да, ще ви уведомяваме на всяка стъпка – от получаването на устройството до неговото изпращане обратно.',
        ],
        [
            'q' => 'Имате ли физически магазин?',
            'a' => 'Да, нашият сервиз се намира в Благоевград. Можете да ни посетите лично или да използвате куриерската ни услуга.',
        ],
        [
            'q' => 'Колко е отстъпката при онлайн поръчка?',
            'a' => 'При онлайн поръчка получавате 10% отстъпка от стойността на ремонта.',
        ],
        [
            'q' => 'Какво се случва, ако повредата е неремонтируема?',
            'a' => 'Ако установим, че устройството не може да бъде ремонтирано, ще ви уведомим и ще го върнем безплатно.',
        ],
    ],
    'pricing_table' => [
        ['service' => 'Смяна на дисплей', 'iphone11' => 'от 89 €', 'iphone12' => 'от 109 €', 'iphone13' => 'от 129 €', 'iphone14' => 'от 149 €', 'iphone15' => 'от 169 €', 'iphone16' => 'от 189 €'],
        ['service' => 'Смяна на батерия', 'iphone11' => 'от 49 €', 'iphone12' => 'от 55 €', 'iphone13' => 'от 59 €', 'iphone14' => 'от 65 €', 'iphone15' => 'от 69 €', 'iphone16' => 'от 75 €'],
        ['service' => 'Ремонт Face ID', 'iphone11' => 'от 119 €', 'iphone12' => 'от 129 €', 'iphone13' => 'от 139 €', 'iphone14' => 'от 149 €', 'iphone15' => 'от 159 €', 'iphone16' => 'от 169 €'],
        ['service' => 'Ремонт камера', 'iphone11' => 'от 69 €', 'iphone12' => 'от 79 €', 'iphone13' => 'от 89 €', 'iphone14' => 'от 99 €', 'iphone15' => 'от 109 €', 'iphone16' => 'от 119 €'],
    ],
    'model_problems' => [
        '11' => ['Счупен дисплей', 'Бърза разрядка на батерия', 'Face ID спира да работи', 'Проблеми с камерата', 'Заглушен звук'],
        '12' => ['Пукнато стъкло', 'Влошена батерия', 'Проблем с Face ID', 'Замъглена камера', 'Проблеми с Wi-Fi'],
        '13' => ['Счупен OLED дисплей', 'Бърз разряд', 'Face ID грешки', 'Камера не фокусира', 'Мигащ екран'],
        '14' => ['Счупен дисплей', 'Батерия под 80%', 'Face ID проблеми', 'Камера шум', 'Проблеми със зареждане'],
        '15' => ['Напукан гръб', 'Батерия с бърз разряд', 'Dynamic Island проблеми', 'Камера без автофокус', 'USB-C порт проблеми'],
        '16' => ['Пукнат дисплей', 'Проблеми с вертикалната камера', 'Батерия с кратък живот', 'Face ID и сензори', 'Проблеми със зареждане през USB-C'],
    ],
];
