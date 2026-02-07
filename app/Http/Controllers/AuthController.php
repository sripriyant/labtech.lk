<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Models\Location;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $credentials['username'];

        $attempt = Auth::attempt(['email' => $login, 'password' => $credentials['password']])
            || Auth::attempt(['name' => $login, 'password' => $credentials['password']]);

        if ($attempt) {
            if (Auth::user() && Auth::user()->is_active === false) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Your account is disabled. Please contact support.',
                ])->onlyInput('username');
            }
            $request->session()->regenerate();
            return redirect()->route('billing.index');
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
