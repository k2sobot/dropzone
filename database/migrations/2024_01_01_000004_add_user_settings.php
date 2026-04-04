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
        Schema::table( 'users', function ( Blueprint $table ) {
            // Upload settings (null = use global defaults)
            $table->unsignedBigInteger( 'max_file_size' )->nullable()->after( 'password' );
            $table->integer( 'max_uploads_per_day' )->nullable()->after( 'max_file_size' );
            $table->integer( 'default_expiration' )->nullable()->after( 'max_uploads_per_day' );
            
            // User status
            $table->boolean( 'is_active' )->default( true )->after( 'default_expiration' );
            
            // Quota tracking
            $table->unsignedBigInteger( 'storage_quota_bytes' )->nullable()->after( 'is_active' );
            $table->unsignedBigInteger( 'storage_used_bytes' )->default( 0 )->after( 'storage_quota_bytes' );
        } );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table( 'users', function ( Blueprint $table ) {
            $table->dropColumn( [
                'max_file_size',
                'max_uploads_per_day',
                'default_expiration',
                'is_active',
                'storage_quota_bytes',
                'storage_used_bytes',
            ] );
        } );
    }
};
