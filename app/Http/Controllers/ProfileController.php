<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's profile avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updateAvatar', [
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:50'],
        ]);

        $user = $request->user();
        $previousAvatar = $user->avatar;

        $path = $validated['avatar']->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        if ($previousAvatar && ! str_starts_with($previousAvatar, 'http')) {
            if (Storage::disk('public')->exists($previousAvatar)) {
                Storage::disk('public')->delete($previousAvatar);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }
}
