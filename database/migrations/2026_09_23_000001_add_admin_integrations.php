<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_connections', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->boolean('enabled')->default(false);
            $table->text('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->json('state')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_connection_id')->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('queued');
            $table->unsignedInteger('record_count')->default(0);
            $table->string('message', 500)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['integration_connection_id', 'status']);
        });

        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->text('category')->nullable();
            $table->decimal('retail_price', 14, 4)->nullable();
            $table->decimal('dealer_price', 14, 4)->nullable();
            $table->string('currency_code', 3);
            $table->text('availability')->nullable();
            $table->unsignedInteger('warranty_months')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();
            $table->unique(['provider', 'code']);
        });

        Schema::create('external_documents', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('kind', 30);
            $table->string('external_id');
            $table->string('reference')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total', 14, 2)->nullable();
            $table->string('currency_code', 3);
            $table->date('document_date')->nullable();
            $table->json('payload');
            $table->timestamp('synced_at');
            $table->timestamps();
            $table->unique(['provider', 'kind', 'external_id']);
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->string('currency_code', 3)->primary();
            $table->decimal('units_per_eur', 18, 8);
            $table->date('as_of');
            $table->timestamp('fetched_at');
        });

        Schema::table('business_orders', function (Blueprint $table) {
            $table->string('intake_reference')->nullable()->unique();
            $table->foreignUuid('repair_request_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->string('dedupe_key', 64)->nullable()->unique();
        });
        Schema::table('pricing_benchmarks', function (Blueprint $table) {
            $table->string('external_id')->nullable();
            $table->unique(['pricing_source_id', 'external_id']);
        });
        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->string('external_id')->nullable();
            $table->unique(['source', 'external_id']);
        });

        foreach (['jar', 'ecb', 'price_feed', 'google_reviews'] as $provider) {
            DB::table('integration_connections')->insert([
                'provider' => $provider, 'enabled' => $provider === 'ecb',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        // Keep historic samples visible, but never treat them as market evidence.
        DB::table('pricing_benchmarks')->where('availability_text', 'Synced sample benchmark')->update(['is_active' => false]);
    }

    public function down(): void
    {
        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->dropColumn('external_id');
        });
        Schema::table('pricing_benchmarks', function (Blueprint $table) {
            $table->dropUnique(['pricing_source_id', 'external_id']);
            $table->dropColumn('external_id');
        });
        Schema::table('conversation_messages', fn (Blueprint $table) => $table->dropColumn('dedupe_key'));
        Schema::table('business_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('repair_request_id');
            $table->dropColumn('intake_reference');
        });
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('external_documents');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('integration_runs');
        Schema::dropIfExists('integration_connections');
    }
};
