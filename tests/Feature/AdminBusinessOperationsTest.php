<?php

namespace Tests\Feature;

use App\Models\BusinessOrder;
use App\Models\CommunicationTemplate;
use App\Models\CustomerProfile;
use App\Models\CustomerReview;
use App\Models\InventoryItem;
use App\Models\MarketingPage;
use App\Models\Payment;
use App\Models\ServiceCatalogItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_all_business_sections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/business')
            ->assertOk()
            ->assertSee('Business operations cockpit');

        foreach (['orders', 'customers', 'inventory', 'suppliers', 'payments', 'services', 'communications', 'reviews', 'marketing'] as $resource) {
            $this->actingAs($admin)
                ->get("/admin/business/{$resource}")
                ->assertOk();
        }

        $this->actingAs($admin)
            ->get('/admin/business/reports')
            ->assertOk()
            ->assertSee('Business reports');
    }

    public function test_regular_user_cannot_access_business_operations(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/business')
            ->assertForbidden();
    }

    public function test_admin_can_create_core_business_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $customerResponse = $this->actingAs($admin)->post('/admin/business/customers', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '+359888111222',
            'preferred_channel' => 'whatsapp',
            'status' => 'active',
        ]);

        $customer = CustomerProfile::query()->where('email', 'customer@example.com')->firstOrFail();
        $customerResponse->assertRedirect(route('admin.business.edit', ['customers', $customer]));

        $supplierResponse = $this->actingAs($admin)->post('/admin/business/suppliers', [
            'name' => 'Parts Partner',
            'contact_person' => 'Supplier Contact',
            'lead_time_days' => 2,
            'status' => 'active',
        ]);

        $supplier = Supplier::query()->where('name', 'Parts Partner')->firstOrFail();
        $supplierResponse->assertRedirect(route('admin.business.edit', ['suppliers', $supplier]));

        $inventoryResponse = $this->actingAs($admin)->post('/admin/business/inventory', [
            'supplier_id' => $supplier->id,
            'name' => 'iPhone display test',
            'sku' => 'INV-TEST-01',
            'category' => 'display',
            'quantity_on_hand' => 2,
            'quantity_reserved' => 0,
            'reorder_level' => 1,
            'unit_cost' => 99,
            'currency_code' => 'BGN',
            'warranty_months' => 6,
            'status' => 'active',
        ]);

        $inventory = InventoryItem::query()->where('sku', 'INV-TEST-01')->firstOrFail();
        $inventoryResponse->assertRedirect(route('admin.business.edit', ['inventory', $inventory]));

        $orderResponse = $this->actingAs($admin)->post('/admin/business/orders', [
            'customer_profile_id' => $customer->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+359888111222',
            'device_or_product' => 'iPhone 15 Pro',
            'service_type' => 'repair',
            'status' => 'received',
            'priority' => 'normal',
            'estimated_total' => 180,
            'amount_paid' => 40,
        ]);

        $order = BusinessOrder::query()->where('customer_name', 'Test Customer')->firstOrFail();
        $orderResponse->assertRedirect(route('admin.business.edit', ['orders', $order]));
        $this->assertStringStartsWith('ORD-', $order->order_number);

        $paymentResponse = $this->actingAs($admin)->post('/admin/business/payments', [
            'business_order_id' => $order->id,
            'customer_profile_id' => $customer->id,
            'invoice_number' => 'INV-TEST-01',
            'method' => 'cash',
            'status' => 'deposit',
            'amount' => 40,
            'currency_code' => 'BGN',
        ]);

        $payment = Payment::query()->where('invoice_number', 'INV-TEST-01')->firstOrFail();
        $paymentResponse->assertRedirect(route('admin.business.edit', ['payments', $payment]));
    }

    public function test_admin_can_create_launch_content_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $serviceResponse = $this->actingAs($admin)->post('/admin/business/services', [
            'title' => 'Laptop diagnostics',
            'category' => 'diagnostics',
            'base_price' => 30,
            'is_featured' => '1',
            'is_published' => '1',
            'sort_order' => 20,
            'description' => 'Hardware and software diagnostics before repair.',
        ]);

        $service = ServiceCatalogItem::query()->where('title', 'Laptop diagnostics')->firstOrFail();
        $serviceResponse->assertRedirect(route('admin.business.edit', ['services', $service]));
        $this->assertSame('laptop-diagnostics', $service->slug);

        $templateResponse = $this->actingAs($admin)->post('/admin/business/communications', [
            'name' => 'Waiting parts message',
            'channel' => 'viber',
            'event_key' => 'waiting_parts',
            'body' => 'Hello {{ customer_name }}, we are waiting for a part for order {{ order_number }}.',
            'is_active' => '1',
        ]);

        $template = CommunicationTemplate::query()->where('name', 'Waiting parts message')->firstOrFail();
        $templateResponse->assertRedirect(route('admin.business.edit', ['communications', $template]));

        $reviewResponse = $this->actingAs($admin)->post('/admin/business/reviews', [
            'customer_name' => 'Review Customer',
            'source' => 'google',
            'rating' => 5,
            'review_text' => 'Excellent service and fast communication.',
            'is_featured' => '1',
            'is_published' => '1',
            'reviewed_at' => now()->toDateString(),
        ]);

        $review = CustomerReview::query()->where('customer_name', 'Review Customer')->firstOrFail();
        $reviewResponse->assertRedirect(route('admin.business.edit', ['reviews', $review]));

        $pageResponse = $this->actingAs($admin)->post('/admin/business/marketing', [
            'title' => 'Gaming PC Blagoevgrad',
            'type' => 'landing',
            'meta_title' => 'Gaming PC builds in Blagoevgrad',
            'headline' => 'Custom gaming PCs built and supported locally',
            'is_published' => '1',
        ]);

        $page = MarketingPage::query()->where('title', 'Gaming PC Blagoevgrad')->firstOrFail();
        $pageResponse->assertRedirect(route('admin.business.edit', ['marketing', $page]));
        $this->assertSame('gaming-pc-blagoevgrad', $page->slug);
    }
}
