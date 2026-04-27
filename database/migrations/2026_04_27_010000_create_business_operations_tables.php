<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('preferred_channel', 32)->default('phone');
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamp('last_contacted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('payment_terms')->nullable();
            $table->unsignedSmallInteger('lead_time_days')->default(3);
            $table->string('warranty_terms')->nullable();
            $table->string('status', 32)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category', 48)->default('parts');
            $table->unsignedInteger('quantity_on_hand')->default(0);
            $table->unsignedInteger('quantity_reserved')->default(0);
            $table->unsignedInteger('reorder_level')->default(2);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('currency_code', 8)->default('BGN');
            $table->unsignedSmallInteger('warranty_months')->default(6);
            $table->string('location')->nullable();
            $table->string('status', 32)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('business_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('device_or_product');
            $table->string('service_type', 48)->default('repair');
            $table->string('status', 48)->default('received');
            $table->string('priority', 32)->default('normal');
            $table->decimal('estimated_total', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->timestamp('due_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->string('contact_next_step')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('method', 32)->default('cash');
            $table->string('status', 32)->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('currency_code', 8)->default('BGN');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 48)->default('repair');
            $table->decimal('base_price', 10, 2)->nullable();
            $table->string('price_note')->nullable();
            $table->string('duration_estimate')->nullable();
            $table->string('warranty_terms')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });

        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel', 32)->default('email');
            $table->string('event_key', 80);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['channel', 'event_key']);
        });

        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('source', 48)->default('google');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('review_text');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->date('reviewed_at')->nullable();
            $table->text('public_reply')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('marketing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 48)->default('landing');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('headline')->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_pages');
        Schema::dropIfExists('customer_reviews');
        Schema::dropIfExists('communication_templates');
        Schema::dropIfExists('service_catalog_items');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('business_orders');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customer_profiles');
    }
};
