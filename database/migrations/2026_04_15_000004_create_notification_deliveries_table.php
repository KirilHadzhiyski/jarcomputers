<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('repair_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 40);
            $table->string('target')->nullable();
            $table->string('subject')->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('provider_message_id')->nullable();
            $table->string('response_code', 20)->nullable();
            $table->text('response_body')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
