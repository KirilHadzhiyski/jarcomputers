<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SiteData
{
    public static function content(): array
    {
        return config('site');
    }

    public static function services(): array
    {
        return config('site.services', []);
    }

    public static function models(): array
    {
        return config('site.models', []);
    }

    public static function cities(): array
    {
        return config('site.cities', []);
    }

    public static function steps(): array
    {
        return config('site.steps', []);
    }

    public static function trustItems(): array
    {
        return config('site.trust_items', []);
    }

    public static function whyUs(): array
    {
        return config('site.why_us', []);
    }

    public static function messagingChannels(): array
    {
        return config('site.messaging_channels', []);
    }

    public static function reviewStats(): array
    {
        return config('reviews', []);
    }

    public static function aggregateReview(): array
    {
        return config('reviews.aggregate', []);
    }

    public static function reviewPlatforms(): array
    {
        return config('reviews.platforms', []);
    }

    public static function pricingTable(): array
    {
        return collect(config('site.pricing_table', []))
            ->map(fn (array $row) => [
                'service' => $row['service'],
                'iphone11' => self::formatPrice($row['iphone11']),
                'iphone12' => self::formatPrice($row['iphone12']),
                'iphone13' => self::formatPrice($row['iphone13']),
                'iphone14' => self::formatPrice($row['iphone14']),
                'iphone15' => self::formatPrice($row['iphone15'] ?? null),
                'iphone16' => self::formatPrice($row['iphone16'] ?? null),
            ])
            ->all();
    }

    public static function formatPrice(int|float|string|null $value, bool $withFrom = true): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_string($value)) {
            $normalized = trim((string) preg_replace('/\s+/u', ' ', $value));
            $amount = trim((string) preg_replace('/[^\d.,]/u', '', $normalized));
            $hasFromPrefix = str_starts_with(mb_strtolower($normalized), 'от ');

            if ($amount === '') {
                return self::replaceCurrency($normalized);
            }

            return (($withFrom || $hasFromPrefix) ? 'от ' : '').$amount.' €';
        }

        return ($withFrom ? 'от ' : '').$value.' €';
    }

    public static function replaceCurrency(string $value): string
    {
        return str_replace([' лв', 'лв'], [' €', '€'], $value);
    }

    public static function faqHome(): array
    {
        return config('site.faq_home', []);
    }

    public static function faqExtended(): array
    {
        return array_merge(self::faqHome(), config('site.faq_extra', []));
    }

    public static function service(string $slug): ?array
    {
        return collect(self::services())->firstWhere('slug', $slug);
    }

    public static function model(string $slug): ?array
    {
        return collect(self::models())->firstWhere('slug', $slug);
    }

    public static function modelBySeries(string $series): ?array
    {
        return collect(self::models())->firstWhere('series', $series);
    }

    public static function city(string $slug): ?array
    {
        return collect(self::cities())->firstWhere('slug', $slug);
    }

    public static function seoSlug(array $service, array $model): string
    {
        return Str::replaceLast('-iphone', '', $service['slug']).'-iphone-'.$model['series'];
    }

    public static function seoPage(string $slug): ?array
    {
        foreach (self::services() as $service) {
            foreach (self::models() as $model) {
                if (self::seoSlug($service, $model) === $slug) {
                    return [
                        'slug' => $slug,
                        'service' => $service,
                        'model' => $model,
                    ];
                }
            }
        }

        return null;
    }

    public static function pricingForModel(string $series): array
    {
        return collect(self::pricingTable())
            ->map(function (array $row) use ($series) {
                return [
                    'service' => $row['service'],
                    'price' => $row["iphone{$series}"] ?? null,
                ];
            })
            ->all();
    }

    public static function modelProblems(string $series): array
    {
        return config("site.model_problems.{$series}", config('site.model_problems.11', []));
    }

    public static function serviceFaq(array $service): array
    {
        return [
            [
                'q' => 'Колко струва '.Str::lower($service['name']).' на iPhone?',
                'a' => 'Цената за '.Str::lower($service['name']).' започва от '.self::formatPrice($service['price_from']).'. Окончателната цена зависи от модела и състоянието на устройството. Диагностиката е безплатна.',
            ],
            [
                'q' => 'Колко време отнема ремонтът?',
                'a' => 'Повечето ремонти се извършват в рамките на 24–48 часа след получаване на устройството.',
            ],
            [
                'q' => 'Предлагате ли гаранция?',
                'a' => 'Да, предлагаме гаранция до 12 месеца за '.Str::lower($service['name']).'.',
            ],
            [
                'q' => 'Как работи куриерската услуга?',
                'a' => 'Изпращаме куриер до вашия адрес, за да вземе устройството. След ремонта го връщаме по същия начин – безплатно в двете посоки.',
            ],
            [
                'q' => 'Какви части използвате?',
                'a' => 'Използваме качествени съвместими и оригинални части, тествани за надеждност и дълготрайност.',
            ],
        ];
    }

    public static function modelFaq(array $model): array
    {
        return [
            [
                'q' => "Колко струва ремонт на {$model['name']}?",
                'a' => 'Цените за ремонт на '.$model['name'].' започват от '.self::formatPrice(49).' за смяна на батерия. Окончателната цена зависи от вида на ремонта.',
            ],
            [
                'q' => "Колко време отнема ремонт на {$model['name']}?",
                'a' => 'Повечето ремонти се извършват в рамките на 24–48 часа.',
            ],
            [
                'q' => 'Предлагате ли куриер?',
                'a' => 'Да, изпращаме куриер до вашия адрес безплатно в двете посоки.',
            ],
            [
                'q' => 'Какви части използвате?',
                'a' => 'Използваме качествени съвместими части с гаранция до 12 месеца.',
            ],
        ];
    }

    public static function cityFaq(array $city): array
    {
        return [
            [
                'q' => "Как мога да изпратя телефона си от {$city['name']}?",
                'a' => "Попълвате онлайн заявка и ние изпращаме куриер до вашия адрес в {$city['name']}. Ремонтираме устройството и го връщаме по куриер – безплатно в двете посоки.",
            ],
            [
                'q' => "Колко време отнема доставката от {$city['name']}?",
                'a' => "Обикновено куриерът взима устройството на следващия работен ден. Ремонтът отнема 24–48 часа, а връщането – още 1 работен ден.",
            ],
            [
                'q' => 'Извършвате ли ремонти на място?',
                'a' => 'Не, всички ремонти се извършват в нашия сервиз в Благоевград. Но благодарение на бързата куриерска услуга, целият процес отнема само 3–5 работни дни.',
            ],
            [
                'q' => 'Безплатен ли е куриерът?',
                'a' => 'Да, куриерската услуга е безплатна в двете посоки за клиенти от цяла България.',
            ],
            [
                'q' => 'Какво се случва, ако не одобря ремонта?',
                'a' => 'Връщаме устройството безплатно. Диагностиката е безплатна и не дължите нищо.',
            ],
        ];
    }

    public static function seoFaq(array $service, array $model): array
    {
        return [
            [
                'q' => 'Колко струва '.Str::lower($service['name'])." на {$model['name']}?",
                'a' => 'Цената за '.Str::lower($service['name']).' на '.$model['name'].' зависи от диагностиката. Цените започват от '.self::formatPrice($service['price_from']).'. Диагностиката е безплатна.',
            ],
            [
                'q' => 'Колко време отнема?',
                'a' => 'Повечето ремонти се извършват в рамките на 24–48 часа.',
            ],
            [
                'q' => 'Предлагате ли гаранция?',
                'a' => 'Да, гаранция до 12 месеца за '.Str::lower($service['name'])." на {$model['name']}.",
            ],
            [
                'q' => 'Как да изпратя телефона си?',
                'a' => 'Попълвате онлайн заявка и ние изпращаме куриер до вашия адрес безплатно.',
            ],
        ];
    }

    public static function staticPageSummary(): array
    {
        return [
            'site' => self::content(),
            'services' => self::services(),
            'models' => self::models(),
            'cities' => self::cities(),
            'steps' => self::steps(),
            'trustItems' => self::trustItems(),
            'whyUs' => self::whyUs(),
            'messagingChannels' => self::messagingChannels(),
            'faqHome' => self::faqHome(),
            'pricingTable' => self::pricingTable(),
            'reviewStats' => self::reviewStats(),
            'aggregateReview' => self::aggregateReview(),
            'reviewPlatforms' => self::reviewPlatforms(),
        ];
    }

    public static function aboutStats(): array
    {
        $aggregate = self::aggregateReview();
        $google = collect(self::reviewPlatforms())->firstWhere('key', 'google-maps');

        return [
            ['label' => 'Години опит', 'value' => '20+'],
            ['label' => 'Google рейтинг', 'value' => ($google['rating_value'] ?? '4.7').'/'.($google['rating_scale'] ?? '5')],
            ['label' => 'Общо оценки', 'value' => (string) ($aggregate['reviews_count'] ?? '147')],
            ['label' => 'Месеца гаранция', 'value' => 'до 12'],
        ];
    }

    public static function modelsForSelect(): array
    {
        return collect(self::models())
            ->pluck('name')
            ->push('Друг модел')
            ->values()
            ->all();
    }

    public static function serviceBasesPattern(): string
    {
        return collect(self::services())
            ->map(fn (array $service) => preg_quote(Str::replaceLast('-iphone', '', $service['slug']), '/'))
            ->implode('|');
    }

    public static function modelSeriesPattern(): string
    {
        return collect(self::models())
            ->pluck('series')
            ->map(fn (string $series) => preg_quote($series, '/'))
            ->implode('|');
    }

    public static function serviceSlugs(): Collection
    {
        return collect(self::services())->pluck('slug');
    }

    public static function modelSlugs(): Collection
    {
        return collect(self::models())->pluck('slug');
    }

    public static function citySlugs(): Collection
    {
        return collect(self::cities())->pluck('slug');
    }
}
