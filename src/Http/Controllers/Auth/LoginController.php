<?php

namespace Ginkelsoft\Buildora\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     * If already authenticated, redirect to the dashboard.
     *
     * @return View|RedirectResponse
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('buildora.dashboard');
        }

        return view('buildora::auth.login');
    }

    /**
     * Attempt to log in the user using provided credentials.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('buildora.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials are invalid.',
        ]);
    }

    /**
     * Log out the current user and invalidate the session.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('buildora.login'));
    }
}
