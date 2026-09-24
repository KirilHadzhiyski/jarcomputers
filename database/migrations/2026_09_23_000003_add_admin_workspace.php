<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_configurations', function (Blueprint $table) {
            $table->json('cross_border_data')->nullable();
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable();
        });
        Schema::create('skroutz_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_configuration_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('attributes')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skroutz_listings');
        Schema::table('conversations', fn (Blueprint $table) => $table->dropColumn('read_at'));
        Schema::table('pricing_configurations', fn (Blueprint $table) => $table->dropColumn('cross_border_data'));
    }
};
