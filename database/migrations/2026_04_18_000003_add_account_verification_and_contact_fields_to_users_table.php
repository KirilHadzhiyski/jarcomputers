<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable()->after('email');
            $table->string('preferred_contact_channel')->default('email')->after('phone');
            $table->string('email_verification_code')->nullable()->after('remember_token');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'preferred_contact_channel',
                'email_verification_code',
                'email_verification_expires_at',
            ]);
        });
    }
};
