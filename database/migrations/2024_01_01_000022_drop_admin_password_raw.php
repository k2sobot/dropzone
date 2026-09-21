<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_settings')) {
            DB::table('admin_settings')->where('key', 'admin_password_raw')->delete();
        }
    }

    public function down(): void
    {
        // Irreversible security cleanup.
    }
};
