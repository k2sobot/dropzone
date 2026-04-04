<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display users list.
     */
    public function index(): View
    {
        $users = User::with( 'roles' )->paginate( 20 );

        return view( 'admin.users.index', [
            'users' => $users,
        ] );
    }

    /**
     * Show create user form.
     */
    public function create(): View
    {
        $roles = Role::pluck( 'name', 'name' )->all();
        $globalMaxFileSize = (int) ( AdminSetting::get( 'max_file_size', 104857600 ) / 1048576 );
        $globalDefaultExpiration = (int) AdminSetting::get( 'default_expiration', 24 );

        return view( 'admin.users.create', [
            'roles' => $roles,
            'globalMaxFileSize' => $globalMaxFileSize,
            'globalDefaultExpiration' => $globalDefaultExpiration,
        ] );
    }

    /**
     * Store new user.
     */
    public function store( Request $request )
    {
        $request->validate( [
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|min:8|confirmed',
            'roles'              => 'required|array',
            'max_file_size_mb'   => 'nullable|integer|min:1|max:1024',
            'max_uploads_per_day' => 'nullable|integer|min:0',
            'default_expiration' => 'nullable|integer|min:1|max:720',
            'storage_quota_gb'   => 'nullable|numeric|min:0',
        ] );

        $user = User::create( [
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make( $request->password ),
            'max_file_size'      => $request->filled( 'max_file_size_mb' )
                ? $request->max_file_size_mb * 1048576
                : null,
            'max_uploads_per_day' => $request->filled( 'max_uploads_per_day' )
                ? $request->max_uploads_per_day
                : null,
            'default_expiration' => $request->filled( 'default_expiration' )
                ? $request->default_expiration
                : null,
            'storage_quota_bytes' => $request->filled( 'storage_quota_gb' )
                ? (int) ( $request->storage_quota_gb * 1073741824 )
                : null,
            'is_active'          => $request->boolean( 'is_active', true ),
        ] );

        $user->assignRole( $request->roles );

        return redirect()
            ->route( 'admin.users.index' )
            ->with( 'success', 'User created successfully.' );
    }

    /**
     * Show edit user form.
     */
    public function edit( User $user ): View
    {
        $roles = Role::pluck( 'name', 'name' )->all();
        $userRoles = $user->roles->pluck( 'name' )->all();
        $globalMaxFileSize = (int) ( AdminSetting::get( 'max_file_size', 104857600 ) / 1048576 );
        $globalDefaultExpiration = (int) AdminSetting::get( 'default_expiration', 24 );

        return view( 'admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'globalMaxFileSize' => $globalMaxFileSize,
            'globalDefaultExpiration' => $globalDefaultExpiration,
        ] );
    }

    /**
     * Update user.
     */
    public function update( Request $request, User $user )
    {
        $request->validate( [
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email,' . $user->id,
            'roles'              => 'required|array',
            'max_file_size_mb'   => 'nullable|integer|min:1|max:1024',
            'max_uploads_per_day' => 'nullable|integer|min:0',
            'default_expiration' => 'nullable|integer|min:1|max:720',
            'storage_quota_gb'   => 'nullable|numeric|min:0',
        ] );

        $data = [
            'name'               => $request->name,
            'email'              => $request->email,
            'is_active'          => $request->boolean( 'is_active', true ),
            'max_file_size'      => $request->filled( 'max_file_size_mb' )
                ? $request->max_file_size_mb * 1048576
                : null,
            'max_uploads_per_day' => $request->filled( 'max_uploads_per_day' )
                ? $request->max_uploads_per_day
                : null,
            'default_expiration' => $request->filled( 'default_expiration' )
                ? $request->default_expiration
                : null,
            'storage_quota_bytes' => $request->filled( 'storage_quota_gb' )
                ? (int) ( $request->storage_quota_gb * 1073741824 )
                : null,
        ];

        if ( $request->filled( 'password' ) ) {
            $request->validate( [
                'password' => 'min:8|confirmed',
            ] );
            $data['password'] = Hash::make( $request->password );
        }

        $user->update( $data );
        $user->syncRoles( $request->roles );

        return redirect()
            ->route( 'admin.users.index' )
            ->with( 'success', 'User updated successfully.' );
    }

    /**
     * Delete user.
     */
    public function destroy( User $user )
    {
        // Prevent deleting yourself
        if ( $user->id === auth()->id() ) {
            return back()->with( 'error', 'Cannot delete your own account.' );
        }

        $user->delete();

        return redirect()
            ->route( 'admin.users.index' )
            ->with( 'success', 'User deleted.' );
    }
}
