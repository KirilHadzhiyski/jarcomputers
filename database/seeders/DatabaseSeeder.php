<?php

namespace Database\Seeders;

use App\Models\BusinessOrder;
use App\Models\CommunicationTemplate;
use App\Models\CustomerProfile;
use App\Models\CustomerReview;
use App\Models\InventoryItem;
use App\Models\MarketingPage;
use App\Models\Payment;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Models\ServiceCatalogItem;
use App\Models\Supplier;
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

        $customer = CustomerProfile::query()->updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'user_id' => User::query()->where('email', 'test@example.com')->value('id'),
                'name' => 'Local Test Client',
                'phone' => '+359888123456',
                'preferred_channel' => 'viber',
                'status' => 'active',
                'last_contacted_at' => now()->subDay(),
                'notes' => 'Seed customer for local admin smoke tests.',
            ],
        );

        $supplier = Supplier::query()->updateOrCreate(
            ['name' => 'JAR Parts Primary Supplier'],
            [
                'contact_person' => 'Parts desk',
                'email' => 'parts@example.com',
                'phone' => '+359888000111',
                'website' => 'https://example.com',
                'payment_terms' => 'Net 7',
                'lead_time_days' => 2,
                'warranty_terms' => '6 months replacement warranty',
                'status' => 'active',
            ],
        );

        InventoryItem::query()->updateOrCreate(
            ['sku' => 'IPH-14-BAT-A'],
            [
                'supplier_id' => $supplier->id,
                'name' => 'iPhone 14 battery - premium',
                'category' => 'battery',
                'quantity_on_hand' => 3,
                'quantity_reserved' => 1,
                'reorder_level' => 2,
                'unit_cost' => 58,
                'currency_code' => 'BGN',
                'warranty_months' => 6,
                'location' => 'Shelf A1',
                'status' => 'active',
            ],
        );

        $order = BusinessOrder::query()->updateOrCreate(
            ['order_number' => 'ORD-SEED-0001'],
            [
                'customer_profile_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'device_or_product' => 'iPhone 14 battery replacement',
                'service_type' => 'repair',
                'status' => 'diagnostics',
                'priority' => 'normal',
                'estimated_total' => 120,
                'amount_paid' => 40,
                'due_at' => now()->addDay(),
                'contact_next_step' => 'Confirm final price before repair.',
                'notes' => 'Customer wants same-day pickup if possible.',
            ],
        );

        Payment::query()->updateOrCreate(
            ['invoice_number' => 'INV-SEED-0001'],
            [
                'business_order_id' => $order->id,
                'customer_profile_id' => $customer->id,
                'method' => 'cash',
                'status' => 'deposit',
                'amount' => 40,
                'currency_code' => 'BGN',
                'due_date' => now()->toDateString(),
                'reference' => 'Deposit at reception',
            ],
        );

        ServiceCatalogItem::query()->updateOrCreate(
            ['slug' => 'iphone-battery-replacement'],
            [
                'title' => 'iPhone battery replacement',
                'category' => 'iphone',
                'base_price' => 89,
                'price_note' => 'Final price depends on model and part grade.',
                'duration_estimate' => 'Same day when part is in stock',
                'warranty_terms' => '6 months',
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 10,
                'description' => 'Premium battery replacement workflow with diagnostics before handover.',
                'seo_title' => 'iPhone battery replacement in Blagoevgrad',
                'seo_description' => 'Fast iPhone battery replacement with diagnostics, warranty, and clear pricing.',
            ],
        );

        CommunicationTemplate::query()->updateOrCreate(
            ['channel' => 'email', 'event_key' => 'order_ready'],
            [
                'name' => 'Order ready for pickup',
                'subject' => 'Your JAR Computers order is ready',
                'body' => 'Hello {{ customer_name }}, your order {{ order_number }} is ready for pickup. Total due: {{ balance_due }} BGN.',
                'is_active' => true,
            ],
        );

        CustomerReview::query()->updateOrCreate(
            ['customer_name' => 'Seed Google Review'],
            [
                'source' => 'google',
                'rating' => 5,
                'review_text' => 'Fast service, clear communication, and the phone works perfectly.',
                'is_featured' => true,
                'is_published' => true,
                'reviewed_at' => now()->subDays(5)->toDateString(),
                'public_reply' => 'Thank you for trusting JAR Computers.',
            ],
        );

        MarketingPage::query()->updateOrCreate(
            ['slug' => 'iphone-repair-blagoevgrad-launch'],
            [
                'title' => 'iPhone Repair Blagoevgrad Launch Page',
                'type' => 'landing',
                'meta_title' => 'iPhone repair in Blagoevgrad | JAR Computers',
                'meta_description' => 'Diagnostics, parts, warranty, and fast communication for iPhone repair in Blagoevgrad.',
                'headline' => 'Fast iPhone repair with clear status updates',
                'body' => 'Launch landing page draft for local iPhone repair demand.',
                'cta_label' => 'Request repair',
                'cta_url' => '/tickets/create',
                'is_published' => false,
                'published_at' => null,
            ],
        );
    }
}
