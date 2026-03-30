<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Profile Controller
 *
 * Handles user profile management and password changes
 * Available for all authenticated users
 */
class ProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Log activity
        activity()
            ->causedBy($user)
            ->withProperties([
                'action' => 'password_changed',
                'user_id' => $user->id,
            ])
            ->log('password_changed');

        return redirect()->route('profile.index')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $oldData = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ];

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->save();

        // Log activity
        activity()
            ->causedBy($user)
            ->withProperties([
                'action' => 'profile_updated',
                'user_id' => $user->id,
                'old' => $oldData,
                'new' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
            ])
            ->log('profile_updated');

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }
}
