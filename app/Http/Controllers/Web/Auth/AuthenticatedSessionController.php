<?php
namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $user = auth('web')->user();

        // Prevent admin from logging in via user login
        if ($request->role === 'user' && $user->role === 'admin') {
            Auth::guard('web')->logout();
            return redirect()->back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Role-based redirect
        if ($user->role === 'admin') {
            return redirect()->intended(route('dashboard'));
        }

        if ($user->role === 'user') {
            return redirect()->intended(route('home'));
        }

        // Optional fallback
        return redirect()->route('login')->withErrors([
            'email' => 'Your account role is not recognized.',
        ]);
    }


    public function admin_login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $user = auth('web')->user();


        // Role-based redirect
        if ($user->role === 'admin') {
            return redirect()->intended(route('dashboard'));
        }


    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
