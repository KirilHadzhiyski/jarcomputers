<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->string('source_auth_type', 24)->default('none')->after('source_url');
            $table->text('source_credentials')->nullable()->after('source_auth_type');
        });

        Schema::table('workflow_runs', function (Blueprint $table) {
            $table->string('trigger_type', 24)->default('manual')->after('triggered_by');
            $table->unsignedSmallInteger('attempt')->default(0)->after('status');
            $table->json('metrics')->nullable()->after('ai_cost');
        });

        Schema::create('workflow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_run_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('ean', 32)->nullable();
            $table->string('gtin', 32)->nullable();
            $table->string('mpn')->nullable();
            $table->string('title');
            $table->string('category')->nullable();
            $table->decimal('price', 14, 4)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('availability', 80)->nullable();
            $table->decimal('rating', 5, 2)->nullable();
            $table->string('promotion')->nullable();
            $table->text('source_url')->nullable();
            $table->string('fingerprint', 64);
            $table->string('status', 24)->default('collected');
            $table->decimal('confidence', 5, 4)->nullable();
            $table->json('data')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();
            $table->unique(['workflow_run_id', 'fingerprint']);
            $table->index(['workflow_id', 'sku']);
            $table->index(['workflow_id', 'ean']);
        });

        Schema::create('workflow_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 48);
            $table->string('severity', 16)->default('warning');
            $table->string('status', 16)->default('open');
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'severity', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_alerts');
        Schema::dropIfExists('workflow_records');

        Schema::table('workflow_runs', function (Blueprint $table) {
            $table->dropColumn(['trigger_type', 'attempt', 'metrics']);
        });

        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['source_auth_type', 'source_credentials']);
        });
    }
};
