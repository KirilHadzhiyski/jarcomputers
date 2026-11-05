<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status', 24)->default('active')->after('role');
            $table->timestamp('suspended_until')->nullable()->after('account_status');
            $table->timestamp('deactivated_at')->nullable()->after('suspended_until');
            $table->json('admin_metadata')->nullable()->after('deactivated_at');
            $table->index(['account_status', 'suspended_until']);
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 80)->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->string('currency_code', 3)->default('EUR');
            $table->unsignedInteger('workflow_limit')->default(1);
            $table->unsignedBigInteger('records_per_month')->default(1000);
            $table->decimal('ai_budget', 12, 2)->default(0);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('client_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 24)->default('trial');
            $table->timestamp('starts_at');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('external_provider', 50)->nullable();
            $table->string('external_customer_id')->nullable();
            $table->string('external_subscription_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'renews_at']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency_code', 3)->default('EUR');
            $table->string('status', 24)->default('pending');
            $table->string('provider', 50)->nullable();
            $table->string('external_reference')->nullable()->unique();
            $table->timestamp('billed_at')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['status', 'billed_at']);
        });

        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type', 24);
            $table->string('provider', 80);
            $table->string('model')->nullable();
            $table->decimal('quantity', 18, 6)->default(0);
            $table->string('unit', 40)->default('request');
            $table->decimal('cost', 14, 6)->default(0);
            $table->string('currency_code', 3)->default('EUR');
            $table->timestamp('occurred_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'occurred_at']);
            $table->index(['provider', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_records');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('client_subscriptions');
        Schema::dropIfExists('subscription_plans');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_status', 'suspended_until']);
            $table->dropColumn(['account_status', 'suspended_until', 'deactivated_at', 'admin_metadata']);
        });
    }
};
