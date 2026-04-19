<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->string('email', 120)->nullable()->after('phone');
            $table->string('source_channel', 40)->default('website')->after('source_page');
            $table->boolean('gdpr_consent')->default(false)->after('source_channel');
            $table->json('meta')->nullable()->after('gdpr_consent');
        });
    }

    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'source_channel',
                'gdpr_consent',
                'meta',
            ]);
        });
    }
};
