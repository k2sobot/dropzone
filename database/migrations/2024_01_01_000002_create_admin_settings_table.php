<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('admin_settings')->insert([
            ['key' => 'background_image', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_name', 'value' => 'Dropzone', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_file_size', 'value' => '104857600', 'created_at' => now(), 'updated_at' => now()], // 100MB
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
