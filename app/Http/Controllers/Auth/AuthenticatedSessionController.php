<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
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
   public function store(Request $request)
{
    // gunakan authenticate() bila tersedia (Breeze / Fortify)
    if (method_exists($request, 'authenticate')) {
        $request->authenticate();
    } else {
        $credentials = $request->only('email', 'password');
        if (! Auth::attempt($credentials, $request->filled('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')]);
        }
    }

    $request->session()->regenerate();

    if (! Auth::check()) {
        return redirect()->route('login')->withErrors(['auth' => 'Authentication failed.']);
    }

    $user = Auth::user();

    // Redirect aman berdasarkan role (pastikan route dengan nama ini terdaftar)
    if ($user->role === 'admin') {
        return redirect()->intended(route('admin.tickets.index'));
    }

    if ($user->role === 'officer') {
        return redirect()->intended(route('officer.tickets.index'));
    }

    return redirect()->intended(RouteServiceProvider::HOME);
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
