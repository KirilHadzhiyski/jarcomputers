<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_market_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('source_key')->unique();
            $table->string('base_url')->nullable();
            $table->string('input_type', 24)->default('hybrid');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_sources');
    }
};
