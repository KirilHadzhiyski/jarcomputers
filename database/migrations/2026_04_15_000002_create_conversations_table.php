<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('repair_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 40);
            $table->string('customer_name', 120)->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->string('customer_email', 120)->nullable();
            $table->string('external_conversation_id')->nullable();
            $table->string('external_user_id')->nullable();
            $table->string('status', 30)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['channel', 'external_conversation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
