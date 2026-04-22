<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitePagesTest extends TestCase
{
    public function test_primary_pages_load_successfully(): void
    {
        foreach ([
            '/',
            '/remont-iphone',
            '/smqna-displei-iphone',
            '/remont-iphone-13',
            '/remont-iphone-16',
            '/remont-iphone-sofia',
            '/smqna-bateria-iphone-14',
            '/smqna-displei-iphone-16',
            '/kontakti',
            '/ceni',
            '/za-nas',
            '/chzv',
            '/politika-za-poveritelnost',
            '/obshti-usloviya',
            '/sitemap.xml',
            '/robots.txt',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_home_page_contains_primary_call_to_action_review_stats_and_trust_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Професионален ремонт на iPhone', false)
            ->assertSee('Поръчай ремонт', false)
            ->assertSee('Google Maps', false)
            ->assertSee('4.7/5', false)
            ->assertSee('/#warranty', false)
            ->assertSee('/#courier', false)
            ->assertSee('Виж Google отзивите', false);
    }

    public function test_contact_page_uses_the_new_public_email_and_shows_only_viber_as_chat_option(): void
    {
        $this->get('/kontakti')
            ->assertOk()
            ->assertSee('office_bl@jarcomputers.com', false)
            ->assertSee('Viber mobile', false)
            ->assertSee('Viber desktop', false)
            ->assertSee('viber://chat?number=%2B359878369024', false)
            ->assertDontSee('WhatsApp', false);
    }

    public function test_about_page_mentions_company_history_and_distribution_activity(): void
    {
        $this->get('/za-nas')
            ->assertOk()
            ->assertSee('2004', false)
            ->assertSee('представител на JAR Computers', false)
            ->assertSee('дистрибуция', false)
            ->assertSee('Публични отзиви и ревюта', false);
    }

    public function test_unknown_page_returns_custom_404(): void
    {
        $this->get('/nesashtestvuvashta-stranica')
            ->assertNotFound()
            ->assertSee('Страницата не беше намерена', false);
    }

    public function test_model_page_contains_carousel_and_price_links_to_pricing_page(): void
    {
        $this->get('/remont-iphone-11')
            ->assertOk()
            ->assertSee('iPhone 11 до iPhone 16', false)
            ->assertSee('/remont-iphone-16', false)
            ->assertSee('iPhone 15', false)
            ->assertSee('iPhone 16', false)
            ->assertSee('/ceni', false);
    }

    public function test_price_sections_on_service_variants_point_to_the_pricing_page(): void
    {
        foreach ([
            '/remont-iphone',
            '/smqna-displei-iphone',
            '/remont-iphone-sofia',
            '/smqna-displei-iphone-11',
        ] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('/ceni', false);
        }
    }

    public function test_alternate_domain_redirects_to_the_canonical_production_host(): void
    {
        config([
            'communications.domain.canonical_host' => 'jarbl.com',
            'communications.domain.redirect_hosts' => ['jarbl.bg'],
            'communications.domain.force_https' => true,
            'communications.domain.app_url' => 'https://jarbl.com',
        ]);

        $this->call('GET', '/kontakti', ['utm_source' => 'test'], [], [], [
            'HTTP_HOST' => 'jarbl.bg',
            'HTTPS' => 'off',
        ])->assertRedirect('https://jarbl.com/kontakti?utm_source=test');
    }
}
