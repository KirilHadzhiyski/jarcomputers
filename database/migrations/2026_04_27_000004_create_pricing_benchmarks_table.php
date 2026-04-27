<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_benchmarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_configuration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_source_id')->constrained()->cascadeOnDelete();
            $table->decimal('observed_price', 10, 2);
            $table->string('currency_code', 8)->default('EUR');
            $table->boolean('price_includes_vat')->default(true);
            $table->decimal('price_excluding_vat', 10, 2)->nullable();
            $table->decimal('price_including_vat', 10, 2)->nullable();
            $table->string('availability_text')->nullable();
            $table->string('competitor_name')->nullable();
            $table->string('product_title')->nullable();
            $table->text('product_url')->nullable();
            $table->string('input_method', 24)->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_benchmarks');
    }
};
