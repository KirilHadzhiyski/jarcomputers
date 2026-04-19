<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('city', 50)->default('');
            $table->string('model', 50)->default('');
            $table->text('issue');
            $table->string('preferred_contact', 20)->default('phone');
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->string('source_page')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_requests');
    }
};
