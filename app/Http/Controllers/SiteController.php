<?php

namespace App\Http\Controllers;

use App\Support\SiteData;
use Illuminate\Http\Response;

class SiteController extends Controller
{
    public function home()
    {
        return view('pages.home', array_merge(SiteData::staticPageSummary(), [
            'seo' => [
                'title' => 'Професионален ремонт на iPhone | JAR Computers Благоевград',
                'description' => 'Бърз и професионален ремонт на iPhone от JAR Computers Благоевград. Гаранция до 12 месеца, куриерска услуга в цяла България, експресен ремонт 24-48 часа.',
            ],
        ]));
    }

    public function mainService()
    {
        $site = SiteData::content();

        return view('pages.main-service', array_merge(SiteData::staticPageSummary(), [
            'seo' => [
                'title' => "Ремонт на iPhone - професионален сервиз | {$site['brand']}",
                'description' => "Професионален ремонт на iPhone от {$site['brand']}. Смяна на дисплей, батерия, Face ID, камера. Гаранция до 12 месеца, куриер в цяла България.",
            ],
        ]));
    }

    public function service(string $slug)
    {
        $service = SiteData::service($slug);
        abort_if($service === null, 404);

        $site = SiteData::content();

        return view('pages.service', array_merge(SiteData::staticPageSummary(), [
            'service' => $service,
            'faqItems' => SiteData::serviceFaq($service),
            'seo' => [
                'title' => "{$service['name']} iPhone - ".SiteData::formatPrice($service['price_from'])." | {$site['brand']}",
                'description' => "{$service['description']} Гаранция до 12 месеца, безплатна диагностика и куриерска услуга в цяла България от {$site['brand']}.",
            ],
        ]));
    }

    public function model(string $slug)
    {
        $model = SiteData::model($slug);
        abort_if($model === null, 404);

        $site = SiteData::content();

        return view('pages.model', array_merge(SiteData::staticPageSummary(), [
            'model' => $model,
            'problems' => SiteData::modelProblems($model['series']),
            'pricing' => SiteData::pricingForModel($model['series']),
            'faqItems' => SiteData::modelFaq($model),
            'seo' => [
                'title' => "Ремонт {$model['name']} - бързо и с гаранция | {$site['brand']}",
                'description' => "Професионален ремонт на {$model['name']} от {$site['brand']}. Смяна на дисплей, батерия, Face ID, камера. Гаранция до 12 месеца, куриер в цяла България.",
            ],
        ]));
    }

    public function city(string $slug)
    {
        $city = SiteData::city($slug);
        abort_if($city === null, 404);

        $site = SiteData::content();

        return view('pages.city', array_merge(SiteData::staticPageSummary(), [
            'city' => $city,
            'faqItems' => SiteData::cityFaq($city),
            'seo' => [
                'title' => "Ремонт на iPhone {$city['name']} - куриер от {$site['brand']}",
                'description' => "Професионален ремонт на iPhone за {$city['name']} с куриерска услуга от {$site['brand']}. Безплатна диагностика, гаранция до 12 месеца.",
            ],
        ]));
    }

    public function seo(string $serviceBase, string $series)
    {
        $page = SiteData::seoPage("{$serviceBase}-iphone-{$series}");
        abort_if($page === null, 404);

        $site = SiteData::content();

        return view('pages.seo', array_merge(SiteData::staticPageSummary(), [
            'page' => $page,
            'faqItems' => SiteData::seoFaq($page['service'], $page['model']),
            'seo' => [
                'title' => "{$page['service']['name']} {$page['model']['name']} - ".SiteData::formatPrice($page['service']['price_from'])." | {$site['brand']}",
                'description' => "{$page['service']['name']} {$page['model']['name']} от {$site['brand']}. Бързо, с гаранция до 12 месеца и куриерска услуга в цяла България. Безплатна диагностика.",
            ],
        ]));
    }

    public function contact()
    {
        $site = SiteData::content();

        return view('pages.contact', array_merge(SiteData::staticPageSummary(), [
            'seo' => [
                'title' => "Контакти | {$site['brand']}",
                'description' => "Свържете се с {$site['brand']} за ремонт на iPhone. Телефон, имейл, WhatsApp, Viber, Messenger и онлайн заявка. Куриерска услуга в цяла България.",
            ],
        ]));
    }

    public function pricing()
    {
        $site = SiteData::content();

        return view('pages.pricing', array_merge(SiteData::staticPageSummary(), [
            'seo' => [
                'title' => "Цени за ремонт на iPhone | {$site['brand']}",
                'description' => "Ориентировъчни цени за ремонт на iPhone от {$site['brand']}. Смяна на дисплей, батерия, Face ID, камера. Безплатна диагностика.",
            ],
        ]));
    }

    public function about()
    {
        $site = SiteData::content();

        return view('pages.about', array_merge(SiteData::staticPageSummary(), [
            'stats' => SiteData::aboutStats(),
            'values' => [
                ['title' => 'Представител за Благоевград', 'desc' => 'Работим като представител на JAR Computers за Благоевград и обслужваме региона с консултация, доставка и сервиз.'],
                ['title' => 'Дистрибуция и продажби', 'desc' => 'Освен сервиз развиваме дистрибуция на техника, компоненти и периферия за частни клиенти, фирми и институции.'],
                ['title' => 'Сервиз и поддръжка', 'desc' => 'Извършваме диагностика, хардуерен сервиз, ремонти и последваща поддръжка с ясна комуникация и реални срокове.'],
            ],
            'seo' => [
                'title' => "За нас | {$site['brand']}",
                'description' => "{$site['brand']} е сервиз за ремонт на iPhone с физически обект в Благоевград, реални клиентски оценки и куриерско обслужване в цяла България.",
            ],
        ]));
    }

    public function faq()
    {
        $site = SiteData::content();

        return view('pages.faq', array_merge(SiteData::staticPageSummary(), [
            'faqItems' => SiteData::faqExtended(),
            'seo' => [
                'title' => "Често задавани въпроси | {$site['brand']}",
                'description' => "Отговори на често задавани въпроси за ремонт на iPhone от {$site['brand']}. Куриер, гаранция, цени, процес и комуникация.",
            ],
        ]));
    }

    public function privacy()
    {
        $site = SiteData::content();

        return view('pages.legal', array_merge(SiteData::staticPageSummary(), [
            'pageTitle' => 'Политика за поверителност',
            'pageIntro' => 'Тази политика описва как обработваме данните, които подавате чрез формата за ремонт, имейл, телефон и свързаните чат канали.',
            'sections' => [
                [
                    'title' => 'Какви данни събираме',
                    'body' => [
                        'При заявка за ремонт събираме име, телефон, имейл при нужда, град, модел устройство, описание на проблема и предпочитан канал за контакт.',
                        'При съобщения през WhatsApp, Viber и Facebook Messenger съхраняваме съдържанието на съобщението, идентификатора на канала и технически данни за проследяване на разговора.',
                    ],
                ],
                [
                    'title' => 'За какво използваме данните',
                    'body' => [
                        'Използваме данните, за да приемем и обработим заявката за ремонт, да се свържем с вас, да изпратим потвърждение и да проследим историята на комуникацията.',
                        'Данните не се използват за нерелевантен маркетинг и не се продават на трети страни.',
                    ],
                ],
                [
                    'title' => 'Срок на съхранение',
                    'body' => [
                        'Заявките и комуникацията се пазят само доколкото е необходимо за обслужване на ремонта, последваща гаранция и законови счетоводни или търговски изисквания.',
                    ],
                ],
                [
                    'title' => 'Вашите права',
                    'body' => [
                        "Можете да поискате достъп, корекция или изтриване на данни, когато това е приложимо. За контакт използвайте {$site['support_email']} или {$site['phone']}.",
                    ],
                ],
            ],
            'seo' => [
                'title' => "Политика за поверителност | {$site['brand']}",
                'description' => "Политика за поверителност на {$site['brand']} за онлайн заявки, имейл и чат канали.",
            ],
        ]));
    }

    public function terms()
    {
        $site = SiteData::content();

        return view('pages.legal', array_merge(SiteData::staticPageSummary(), [
            'pageTitle' => 'Общи условия',
            'pageIntro' => 'Тези условия уреждат подаването на заявки през сайта, диагностиката, комуникацията и изпълнението на ремонта.',
            'sections' => [
                [
                    'title' => 'Заявка и диагностика',
                    'body' => [
                        'Изпращането на онлайн заявка не представлява окончателно приемане на ремонт. След първичен преглед потвърждаваме наличност, срок и ориентировъчна цена.',
                        'Диагностиката е безплатна, освен ако изрично не е договорено друго за специфичен случай.',
                    ],
                ],
                [
                    'title' => 'Одобрение и ремонт',
                    'body' => [
                        'Ремонтът започва след потвърждение от клиента. Ако не одобрите ремонта, устройството се връща според договорената логистика.',
                        'Срокът за ремонт зависи от наличността на части, сложността на проблема и транспортното време при куриерски заявки.',
                    ],
                ],
                [
                    'title' => 'Гаранция',
                    'body' => [
                        'За приложимите ремонти се предоставя гаранция до 12 месеца според конкретната услуга и вложените части.',
                        'Гаранцията не покрива нови механични, водни или софтуерни повреди, които не са свързани с извършения ремонт.',
                    ],
                ],
                [
                    'title' => 'Комуникационни канали',
                    'body' => [
                        'Сайтът поддържа контакт по телефон, имейл и публични чат канали. Наличието на конкретен канал зависи от активираните акаунти и токени на съответната платформа.',
                    ],
                ],
            ],
            'seo' => [
                'title' => "Общи условия | {$site['brand']}",
                'description' => "Общи условия за заявки за ремонт, диагностика, гаранция и комуникация през {$site['brand']}.",
            ],
        ]));
    }

    public function sitemap(): Response
    {
        return response()
            ->view('pages.sitemap', [
                'urls' => $this->sitemapUrls(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.route('sitemap'),
            '',
        ]), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function notFound()
    {
        $site = SiteData::content();

        return response()->view('errors.404', array_merge(SiteData::staticPageSummary(), [
            'seo' => [
                'title' => "Страницата не е намерена | {$site['brand']}",
                'description' => "Страницата, която търсите, не е налична. Разгледайте услугите на {$site['brand']} за ремонт на iPhone.",
            ],
        ]), 404);
    }

    private function sitemapUrls(): array
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('main-service'), 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => route('contact'), 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => route('pricing'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => route('faq'), 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('terms'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        foreach (SiteData::services() as $service) {
            $urls[] = ['loc' => url($service['slug']), 'changefreq' => 'weekly', 'priority' => '0.8'];
        }

        foreach (SiteData::models() as $model) {
            $urls[] = ['loc' => url($model['slug']), 'changefreq' => 'weekly', 'priority' => '0.8'];
        }

        foreach (SiteData::cities() as $city) {
            $urls[] = ['loc' => url($city['slug']), 'changefreq' => 'weekly', 'priority' => '0.7'];
        }

        foreach (SiteData::services() as $service) {
            foreach (SiteData::models() as $model) {
                $urls[] = [
                    'loc' => url(SiteData::seoSlug($service, $model)),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];
            }
        }

        return $urls;
    }
}
