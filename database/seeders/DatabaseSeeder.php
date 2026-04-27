<?php

namespace Database\Seeders;

use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '+359888123456',
                'preferred_contact_channel' => 'email',
                'password' => 'Password123',
                'role' => 'user',
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'email_verification_expires_at' => null,
            ],
        );

        $greece = PricingMarket::query()->updateOrCreate(
            ['code' => 'GR'],
            [
                'name' => 'Greece',
                'currency_code' => 'EUR',
                'vat_rate' => 24,
                'exchange_rate_to_bgn' => 1.9558,
                'is_active' => true,
            ],
        );

        $romania = PricingMarket::query()->updateOrCreate(
            ['code' => 'RO'],
            [
                'name' => 'Romania',
                'currency_code' => 'RON',
                'vat_rate' => 19,
                'exchange_rate_to_bgn' => 0.3928,
                'is_active' => true,
            ],
        );

        PricingSource::query()->updateOrCreate(
            ['source_key' => 'bestprice-gr'],
            [
                'pricing_market_id' => $greece->id,
                'name' => 'bestprice.gr',
                'base_url' => 'https://www.bestprice.gr',
                'input_type' => 'hybrid',
                'is_active' => true,
            ],
        );

        PricingSource::query()->updateOrCreate(
            ['source_key' => 'skroutz-gr'],
            [
                'pricing_market_id' => $greece->id,
                'name' => 'Skroutz',
                'base_url' => 'https://www.skroutz.gr',
                'input_type' => 'hybrid',
                'is_active' => true,
            ],
        );

        PricingSource::query()->updateOrCreate(
            ['source_key' => 'emag-ro'],
            [
                'pricing_market_id' => $romania->id,
                'name' => 'eMAG',
                'base_url' => 'https://www.emag.ro',
                'input_type' => 'hybrid',
                'is_active' => true,
            ],
        );
    }
}
