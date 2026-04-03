<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create users table
        Schema::create( 'users', function ( Blueprint $table ) {
            $table->id();
            $table->string( 'name' );
            $table->string( 'email' )->unique();
            $table->timestamp( 'email_verified_at' )->nullable();
            $table->string( 'password' );
            $table->rememberToken();
            $table->timestamps();
        } );

        // Create password reset tokens table
        Schema::create( 'password_reset_tokens', function ( Blueprint $table ) {
            $table->string( 'email' )->primary();
            $table->string( 'token' );
            $table->timestamp( 'created_at' )->nullable();
        } );

        // Create sessions table
        Schema::create( 'sessions', function ( Blueprint $table ) {
            $table->string( 'id' )->primary();
            $table->foreignId( 'user_id' )->nullable()->index();
            $table->string( 'ip_address', 45 )->nullable();
            $table->text( 'user_agent' )->nullable();
            $table->longText( 'payload' );
            $table->integer( 'last_activity' )->index();
        } );

        // Create roles and permissions tables (Spatie)
        $teams = config( 'permission.teams' );
        $tableNames = config( 'permission.table_names' );
        $columnNames = config( 'permission.column_names' );

        Schema::create( $tableNames['permissions'], function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->string( 'name' );
            $table->string( 'guard_name' );
            $table->timestamps();
            $table->unique( ['name', 'guard_name'] );
        } );

        Schema::create( $tableNames['roles'], function ( Blueprint $table ) use ( $teams, $columnNames ) {
            $table->bigIncrements( 'id' );
            if ( $teams || config( 'permission.teams' ) ) {
                $table->unsignedBigInteger( $columnNames['team_foreign_id'] )->nullable();
                $table->index( $columnNames['team_foreign_id'], 'roles_team_index' );
            }
            $table->string( 'name' );
            $table->string( 'guard_name' );
            $table->timestamps();
            if ( $teams || config( 'permission.teams' ) ) {
                $table->unique( [$columnNames['team_foreign_id'], 'name', 'guard_name'] );
            } else {
                $table->unique( ['name', 'guard_name'] );
            }
        } );

        Schema::create( $tableNames['model_has_permissions'], function ( Blueprint $table ) use ( $tableNames, $columnNames ) {
            $table->unsignedBigInteger( 'permission_id' );
            $table->string( 'model_type' );
            $table->unsignedBigInteger( $columnNames['model_morph_key'] );
            $table->index( [$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index' );

            $table->foreign( 'permission_id' )
                ->references( 'id' )
                ->on( $tableNames['permissions'] )
                ->onDelete( 'cascade' );

            $table->primary(
                ['permission_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        } );

        Schema::create( $tableNames['model_has_roles'], function ( Blueprint $table ) use ( $tableNames, $columnNames ) {
            $table->unsignedBigInteger( 'role_id' );
            $table->string( 'model_type' );
            $table->unsignedBigInteger( $columnNames['model_morph_key'] );
            $table->index( [$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index' );

            $table->foreign( 'role_id' )
                ->references( 'id' )
                ->on( $tableNames['roles'] )
                ->onDelete( 'cascade' );

            $table->primary(
                ['role_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        } );

        Schema::create( $tableNames['role_has_permissions'], function ( Blueprint $table ) use ( $tableNames ) {
            $table->unsignedBigInteger( 'permission_id' );
            $table->unsignedBigInteger( 'role_id' );

            $table->foreign( 'permission_id' )
                ->references( 'id' )
                ->on( $tableNames['permissions'] )
                ->onDelete( 'cascade' );

            $table->foreign( 'role_id' )
                ->references( 'id' )
                ->on( $tableNames['roles'] )
                ->onDelete( 'cascade' );

            $table->primary( ['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary' );
        } );

        // Create default roles and permissions
        $this->createRolesAndPermissions();
    }

    /**
     * Create default roles and permissions.
     */
    protected function createRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            'view uploads',
            'delete uploads',
            'manage users',
            'manage settings',
            'manage extensions',
            'view analytics',
        ];

        foreach ( $permissions as $name ) {
            Permission::create( [ 'name' => $name ] );
        }

        // Create Admin role with all permissions
        $admin = Role::create( [ 'name' => 'admin' ] );
        $admin->givePermissionTo( $permissions );

        // Create Editor role
        $editor = Role::create( [ 'name' => 'editor' ] );
        $editor->givePermissionTo( [ 'view uploads', 'delete uploads' ] );

        // Create Viewer role
        $viewer = Role::create( [ 'name' => 'viewer' ] );
        $viewer->givePermissionTo( [ 'view uploads' ] );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config( 'permission.table_names' );

        Schema::dropIfExists( $tableNames['role_has_permissions'] );
        Schema::dropIfExists( $tableNames['model_has_roles'] );
        Schema::dropIfExists( $tableNames['model_has_permissions'] );
        Schema::dropIfExists( $tableNames['roles'] );
        Schema::dropIfExists( $tableNames['permissions'] );
        Schema::dropIfExists( 'sessions' );
        Schema::dropIfExists( 'password_reset_tokens' );
        Schema::dropIfExists( 'users' );
    }
};
