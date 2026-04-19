<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('repair_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 40);
            $table->string('direction', 20);
            $table->string('status', 30)->default('received');
            $table->string('provider_message_id')->nullable();
            $table->string('sender_name', 120)->nullable();
            $table->string('sender_handle', 120)->nullable();
            $table->longText('content')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'provider_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
