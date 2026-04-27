<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_markets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->unique();
            $table->string('currency_code', 8)->default('EUR');
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('exchange_rate_to_bgn', 10, 4)->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_markets');
    }
};
