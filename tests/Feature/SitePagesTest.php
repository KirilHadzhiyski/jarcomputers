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

    public function test_home_page_contains_primary_call_to_action_and_review_stats(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Професионален ремонт на iPhone', false)
            ->assertSee('Поръчай ремонт', false)
            ->assertSee('Google Maps', false)
            ->assertSee('4.7/5', false);
    }

    public function test_unknown_page_returns_custom_404(): void
    {
        $this->get('/nesashtestvuvashta-stranica')
            ->assertNotFound()
            ->assertSee('Страницата не беше намерена', false);
    }

    public function test_model_page_contains_carousel_for_all_supported_iphone_series(): void
    {
        $this->get('/remont-iphone-11')
            ->assertOk()
            ->assertSee('iPhone 11 до iPhone 16', false)
            ->assertSee('/remont-iphone-16', false)
            ->assertSee('iPhone 15', false)
            ->assertSee('iPhone 16', false);
    }
}
