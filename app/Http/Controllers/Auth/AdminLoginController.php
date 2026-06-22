<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.admin-login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Only allow admin users
        if (auth()->user()->role !== 'admin') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'You do not have admin access.',
            ]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
