<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('integration_connections')->insert(['provider' => 'skroutz', 'enabled' => false, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('integration_connections')->where('provider', 'skroutz')->delete();
    }
};
