<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view( 'admin.users.create', [
            'roles' => $roles,
        ] );
    }

    /**
     * Store new user.
     */
    public function store( Request $request )
    {
        $request->validate( [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles'    => 'required|array',
        ] );

        $user = User::create( [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make( $request->password ),
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

        return view( 'admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ] );
    }

    /**
     * Update user.
     */
    public function update( Request $request, User $user )
    {
        $request->validate( [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'required|array',
        ] );

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
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
