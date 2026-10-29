<?php

namespace App\Http\Controllers;

use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        return view('admin.users');
    }

    public function toggleDisabled($id)
    {
        $user = User::findOrFail($id);
        $user->disabled_at = $user->isDisabled() ? null : now();
        $user->save();

        return back()->with('success', 'User status updated.');
    }

    public function editUserRoles($id)
    {
        $user = User::findOrFail($id);

        return view('admin.edit_user_roles', compact('user'));
    }

    public function editUserSubscriptions($id)
    {
        $user = User::findOrFail($id);

        abort_unless($user->isAdmin(), 404);

        return view('admin.edit_user_subscriptions', compact('user'));
    }
}
