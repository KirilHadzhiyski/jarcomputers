<?php

namespace App\Support;

use App\Models\BusinessOrder;
use App\Models\CommunicationTemplate;
use App\Models\CustomerProfile;
use App\Models\CustomerReview;
use App\Models\InventoryItem;
use App\Models\MarketingPage;
use App\Models\Payment;
use App\Models\ServiceCatalogItem;
use App\Models\Supplier;

class AdminBusinessResources
{
    public static function all(): array
    {
        return [
            'orders' => [
                'model' => BusinessOrder::class,
                'title' => 'Orders',
                'singular' => 'order',
                'description' => 'Track every repair, build, diagnostics job, pickup, and next customer contact.',
                'primary' => 'order_number',
                'search' => ['order_number', 'customer_name', 'customer_phone', 'device_or_product'],
                'fields' => [
                    self::field('order_number', 'Order number', 'text', ['nullable', 'string', 'max:80'], table: true, unique: true, generate: 'ORD'),
                    self::select('customer_profile_id', 'Customer profile', [], ['nullable', 'exists:customer_profiles,id'], dynamic: 'customers'),
                    self::select('user_id', 'Linked user account', [], ['nullable', 'exists:users,id'], dynamic: 'users'),
                    self::select('ticket_id', 'Linked support ticket', [], ['nullable', 'exists:tickets,id'], dynamic: 'tickets'),
                    self::field('customer_name', 'Customer name', 'text', ['required', 'string', 'max:160'], table: true),
                    self::field('customer_email', 'Customer email', 'email', ['nullable', 'email', 'max:255']),
                    self::field('customer_phone', 'Customer phone', 'tel', ['nullable', 'string', 'max:60']),
                    self::field('device_or_product', 'Device / product', 'text', ['required', 'string', 'max:180'], table: true),
                    self::select('service_type', 'Service type', BusinessOrder::SERVICE_TYPE_LABELS, ['required', 'in:repair,diagnostics,warranty,gaming_pc,consultation']),
                    self::select('status', 'Status', BusinessOrder::STATUS_LABELS, ['required', 'in:received,diagnostics,waiting_parts,in_progress,ready,delivered,cancelled'], table: true),
                    self::select('priority', 'Priority', BusinessOrder::PRIORITY_LABELS, ['required', 'in:low,normal,high,urgent']),
                    self::field('estimated_total', 'Estimated total', 'number', ['required', 'numeric', 'min:0'], table: true, step: '0.01'),
                    self::field('amount_paid', 'Amount paid', 'number', ['required', 'numeric', 'min:0'], step: '0.01'),
                    self::field('due_at', 'Due at', 'datetime-local', ['nullable', 'date']),
                    self::field('ready_at', 'Ready at', 'datetime-local', ['nullable', 'date']),
                    self::field('picked_up_at', 'Picked up at', 'datetime-local', ['nullable', 'date']),
                    self::field('contact_next_step', 'Next customer contact', 'text', ['nullable', 'string', 'max:180']),
                    self::field('notes', 'Customer-visible notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                    self::field('internal_notes', 'Internal notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'customers' => [
                'model' => CustomerProfile::class,
                'title' => 'Customers',
                'singular' => 'customer',
                'description' => 'Central customer records with contact preference, history context, and internal notes.',
                'primary' => 'name',
                'search' => ['name', 'email', 'phone', 'company'],
                'fields' => [
                    self::select('user_id', 'Linked user account', [], ['nullable', 'exists:users,id'], dynamic: 'users'),
                    self::field('name', 'Name', 'text', ['required', 'string', 'max:160'], table: true),
                    self::field('email', 'Email', 'email', ['nullable', 'email', 'max:255'], table: true, unique: true),
                    self::field('phone', 'Phone', 'tel', ['nullable', 'string', 'max:60'], table: true),
                    self::select('preferred_channel', 'Preferred channel', CustomerProfile::CHANNEL_LABELS, ['required', 'in:phone,email,viber,whatsapp,facebook']),
                    self::field('company', 'Company', 'text', ['nullable', 'string', 'max:160']),
                    self::select('status', 'Status', CustomerProfile::STATUS_LABELS, ['required', 'in:active,lead,vip,blocked'], table: true),
                    self::field('last_contacted_at', 'Last contacted at', 'datetime-local', ['nullable', 'date']),
                    self::field('address', 'Address', 'textarea', ['nullable', 'string', 'max:2000'], span: true),
                    self::field('notes', 'Internal notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'inventory' => [
                'model' => InventoryItem::class,
                'title' => 'Inventory',
                'singular' => 'inventory item',
                'description' => 'Stock control for parts, batteries, displays, laptop parts, and PC components.',
                'primary' => 'name',
                'search' => ['name', 'sku', 'category', 'location'],
                'fields' => [
                    self::select('supplier_id', 'Supplier', [], ['nullable', 'exists:suppliers,id'], dynamic: 'suppliers'),
                    self::field('name', 'Name', 'text', ['required', 'string', 'max:180'], table: true),
                    self::field('sku', 'SKU', 'text', ['required', 'string', 'max:120'], table: true, unique: true),
                    self::select('category', 'Category', InventoryItem::CATEGORY_LABELS, ['required', 'in:parts,display,battery,connector,laptop,gaming_pc,accessory']),
                    self::field('quantity_on_hand', 'Quantity on hand', 'number', ['required', 'integer', 'min:0'], table: true, step: '1'),
                    self::field('quantity_reserved', 'Quantity reserved', 'number', ['required', 'integer', 'min:0'], step: '1'),
                    self::field('reorder_level', 'Reorder level', 'number', ['required', 'integer', 'min:0'], table: true, step: '1'),
                    self::field('unit_cost', 'Unit cost', 'number', ['required', 'numeric', 'min:0'], table: true, step: '0.01'),
                    self::field('currency_code', 'Currency', 'text', ['required', 'string', 'max:8']),
                    self::field('warranty_months', 'Warranty months', 'number', ['required', 'integer', 'min:0'], step: '1'),
                    self::field('location', 'Shelf / location', 'text', ['nullable', 'string', 'max:120']),
                    self::select('status', 'Status', InventoryItem::STATUS_LABELS, ['required', 'in:active,low_stock,ordered,reserved,archived'], table: true),
                    self::field('notes', 'Notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'suppliers' => [
                'model' => Supplier::class,
                'title' => 'Suppliers',
                'singular' => 'supplier',
                'description' => 'Supplier quality, delivery terms, warranty terms, and contact ownership.',
                'primary' => 'name',
                'search' => ['name', 'contact_person', 'email', 'phone'],
                'fields' => [
                    self::field('name', 'Name', 'text', ['required', 'string', 'max:180'], table: true, unique: true),
                    self::field('contact_person', 'Contact person', 'text', ['nullable', 'string', 'max:160'], table: true),
                    self::field('email', 'Email', 'email', ['nullable', 'email', 'max:255']),
                    self::field('phone', 'Phone', 'tel', ['nullable', 'string', 'max:60']),
                    self::field('website', 'Website', 'url', ['nullable', 'url', 'max:255']),
                    self::field('payment_terms', 'Payment terms', 'text', ['nullable', 'string', 'max:160']),
                    self::field('lead_time_days', 'Lead time days', 'number', ['required', 'integer', 'min:0'], table: true, step: '1'),
                    self::field('warranty_terms', 'Warranty terms', 'text', ['nullable', 'string', 'max:160']),
                    self::select('status', 'Status', Supplier::STATUS_LABELS, ['required', 'in:active,backup,paused,blocked'], table: true),
                    self::field('notes', 'Notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'payments' => [
                'model' => Payment::class,
                'title' => 'Invoices & payments',
                'singular' => 'payment',
                'description' => 'Track unpaid balances, deposits, invoices, paid work, and refunds.',
                'primary' => 'invoice_number',
                'search' => ['invoice_number', 'reference', 'notes'],
                'fields' => [
                    self::select('business_order_id', 'Order', [], ['nullable', 'exists:business_orders,id'], dynamic: 'orders'),
                    self::select('customer_profile_id', 'Customer', [], ['nullable', 'exists:customer_profiles,id'], dynamic: 'customers'),
                    self::field('invoice_number', 'Invoice number', 'text', ['nullable', 'string', 'max:120'], table: true, unique: true),
                    self::select('method', 'Method', Payment::METHOD_LABELS, ['required', 'in:cash,card,bank,online']),
                    self::select('status', 'Status', Payment::STATUS_LABELS, ['required', 'in:pending,deposit,paid,refunded,cancelled'], table: true),
                    self::field('amount', 'Amount', 'number', ['required', 'numeric', 'min:0'], table: true, step: '0.01'),
                    self::field('currency_code', 'Currency', 'text', ['required', 'string', 'max:8']),
                    self::field('due_date', 'Due date', 'date', ['nullable', 'date'], table: true),
                    self::field('paid_at', 'Paid at', 'datetime-local', ['nullable', 'date']),
                    self::field('reference', 'Reference', 'text', ['nullable', 'string', 'max:180']),
                    self::field('notes', 'Notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'services' => [
                'model' => ServiceCatalogItem::class,
                'title' => 'Service catalog',
                'singular' => 'service',
                'description' => 'Control public services, pricing notes, warranty promises, and SEO copy.',
                'primary' => 'title',
                'search' => ['title', 'slug', 'category', 'description'],
                'fields' => [
                    self::field('title', 'Title', 'text', ['required', 'string', 'max:180'], table: true),
                    self::field('slug', 'Slug', 'text', ['nullable', 'string', 'max:180'], table: true, unique: true, slugFrom: 'title'),
                    self::select('category', 'Category', ServiceCatalogItem::CATEGORY_LABELS, ['required', 'in:repair,diagnostics,iphone,laptop,gaming_pc,business']),
                    self::field('base_price', 'Base price', 'number', ['nullable', 'numeric', 'min:0'], table: true, step: '0.01'),
                    self::field('price_note', 'Price note', 'text', ['nullable', 'string', 'max:180']),
                    self::field('duration_estimate', 'Duration estimate', 'text', ['nullable', 'string', 'max:120']),
                    self::field('warranty_terms', 'Warranty terms', 'text', ['nullable', 'string', 'max:160']),
                    self::field('is_featured', 'Featured', 'checkbox', ['boolean'], table: true),
                    self::field('is_published', 'Published', 'checkbox', ['boolean'], table: true),
                    self::field('sort_order', 'Sort order', 'number', ['required', 'integer', 'min:0'], step: '1'),
                    self::field('description', 'Description', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                    self::field('seo_title', 'SEO title', 'text', ['nullable', 'string', 'max:180']),
                    self::field('seo_description', 'SEO description', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'communications' => [
                'model' => CommunicationTemplate::class,
                'title' => 'Communication center',
                'singular' => 'template',
                'description' => 'Ready-to-send templates for email, Viber, WhatsApp, Facebook, and SMS.',
                'primary' => 'name',
                'search' => ['name', 'event_key', 'subject', 'body'],
                'fields' => [
                    self::field('name', 'Name', 'text', ['required', 'string', 'max:180'], table: true),
                    self::select('channel', 'Channel', CommunicationTemplate::CHANNEL_LABELS, ['required', 'in:email,viber,whatsapp,facebook,sms'], table: true),
                    self::select('event_key', 'Event', CommunicationTemplate::EVENT_LABELS, ['required', 'in:order_received,diagnostics_ready,waiting_parts,order_ready,payment_reminder,review_request'], table: true),
                    self::field('subject', 'Subject', 'text', ['nullable', 'string', 'max:180']),
                    self::field('body', 'Message body', 'textarea', ['required', 'string', 'max:8000'], span: true),
                    self::field('is_active', 'Active', 'checkbox', ['boolean'], table: true),
                    self::field('last_used_at', 'Last used at', 'datetime-local', ['nullable', 'date']),
                    self::field('notes', 'Notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'reviews' => [
                'model' => CustomerReview::class,
                'title' => 'Reviews',
                'singular' => 'review',
                'description' => 'Curate Google/Facebook/customer reviews and choose what appears publicly.',
                'primary' => 'customer_name',
                'search' => ['customer_name', 'source', 'review_text'],
                'fields' => [
                    self::field('customer_name', 'Customer name', 'text', ['required', 'string', 'max:160'], table: true),
                    self::select('source', 'Source', CustomerReview::SOURCE_LABELS, ['required', 'in:google,facebook,website,manual'], table: true),
                    self::field('rating', 'Rating', 'number', ['required', 'integer', 'min:1', 'max:5'], table: true, step: '1'),
                    self::field('review_text', 'Review text', 'textarea', ['required', 'string', 'max:8000'], span: true),
                    self::field('is_featured', 'Featured', 'checkbox', ['boolean'], table: true),
                    self::field('is_published', 'Published', 'checkbox', ['boolean'], table: true),
                    self::field('reviewed_at', 'Review date', 'date', ['nullable', 'date'], table: true),
                    self::field('public_reply', 'Public reply', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                    self::field('notes', 'Internal notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
            'marketing' => [
                'model' => MarketingPage::class,
                'title' => 'Marketing & SEO',
                'singular' => 'page',
                'description' => 'Plan public pages, campaigns, meta titles, city pages, and launch landing pages.',
                'primary' => 'title',
                'search' => ['title', 'slug', 'meta_title', 'headline'],
                'fields' => [
                    self::field('title', 'Title', 'text', ['required', 'string', 'max:180'], table: true),
                    self::field('slug', 'Slug', 'text', ['nullable', 'string', 'max:180'], table: true, unique: true, slugFrom: 'title'),
                    self::select('type', 'Type', MarketingPage::TYPE_LABELS, ['required', 'in:landing,city,service,campaign,legal'], table: true),
                    self::field('meta_title', 'Meta title', 'text', ['nullable', 'string', 'max:180']),
                    self::field('meta_description', 'Meta description', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                    self::field('headline', 'Headline', 'text', ['nullable', 'string', 'max:180']),
                    self::field('body', 'Body', 'textarea', ['nullable', 'string', 'max:12000'], span: true),
                    self::field('cta_label', 'CTA label', 'text', ['nullable', 'string', 'max:120']),
                    self::field('cta_url', 'CTA URL', 'text', ['nullable', 'string', 'max:255']),
                    self::field('is_published', 'Published', 'checkbox', ['boolean'], table: true),
                    self::field('published_at', 'Published at', 'datetime-local', ['nullable', 'date']),
                    self::field('notes', 'Notes', 'textarea', ['nullable', 'string', 'max:5000'], span: true),
                ],
            ],
        ];
    }

    public static function get(string $key): array
    {
        abort_unless(array_key_exists($key, self::all()), 404);

        return self::all()[$key] + ['key' => $key];
    }

    public static function menu(): array
    {
        return collect(self::all())
            ->map(fn (array $definition, string $key) => [
                'key' => $key,
                'title' => $definition['title'],
                'description' => $definition['description'],
            ])
            ->values()
            ->all();
    }

    private static function field(
        string $name,
        string $label,
        string $type,
        array $rules,
        bool $table = false,
        bool $span = false,
        bool $unique = false,
        ?string $generate = null,
        ?string $slugFrom = null,
        ?string $step = null,
    ): array {
        return compact('name', 'label', 'type', 'rules', 'table', 'span', 'unique', 'generate', 'slugFrom', 'step');
    }

    private static function select(
        string $name,
        string $label,
        array $options,
        array $rules,
        bool $table = false,
        ?string $dynamic = null,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'select',
            'rules' => $rules,
            'table' => $table,
            'span' => false,
            'unique' => false,
            'generate' => null,
            'slugFrom' => null,
            'step' => null,
            'options' => $options,
            'dynamic' => $dynamic,
        ];
    }
}
