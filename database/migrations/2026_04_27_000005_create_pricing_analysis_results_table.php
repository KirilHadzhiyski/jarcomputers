<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_analysis_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_configuration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_market_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('reference_benchmark_count')->default(0);
            $table->decimal('avg_benchmark_price', 10, 2)->nullable();
            $table->decimal('min_benchmark_price', 10, 2)->nullable();
            $table->decimal('max_benchmark_price', 10, 2)->nullable();
            $table->decimal('base_price_market_currency', 10, 2)->nullable();
            $table->decimal('suggested_price_excluding_vat', 10, 2)->nullable();
            $table->decimal('suggested_price_including_vat', 10, 2)->nullable();
            $table->decimal('suggested_price_bgn_equivalent', 10, 2)->nullable();
            $table->decimal('target_margin_amount', 10, 2)->nullable();
            $table->decimal('target_margin_percent', 5, 2)->nullable();
            $table->string('viability_status', 24)->default('borderline');
            $table->text('competition_note')->nullable();
            $table->text('analysis_summary')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['pricing_configuration_id', 'pricing_market_id'], 'pricing_analysis_configuration_market_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_analysis_results');
    }
};
