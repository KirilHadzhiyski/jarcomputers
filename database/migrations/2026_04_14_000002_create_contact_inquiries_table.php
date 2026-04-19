<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('message');
            $table->string('source_page')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
