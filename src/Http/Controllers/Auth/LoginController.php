<?php

namespace Ginkelsoft\Buildora\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     * If not installed, redirect to the installation wizard.
     * If already authenticated, redirect to the dashboard.
     *
     * @return View|RedirectResponse
     */
    public function showLoginForm(): View|RedirectResponse
    {
        // Check if Buildora is installed
        if (!$this->isInstalled()) {
            return redirect()->route('buildora.install');
        }

        if (auth()->check()) {
            return redirect()->route('buildora.dashboard');
        }

        return view('buildora::auth.login');
    }

    /**
     * Check if Buildora is installed.
     * Covers both new installations with lock file and existing installations without.
     *
     * @return bool
     */
    protected function isInstalled(): bool
    {
        // Check 1: Lock file exists (fastest check)
        $lockFile = storage_path('buildora_installed');
        if (File::exists($lockFile)) {
            return true;
        }

        // Check 2: Resources directory exists with Buildora resource files
        $resourceDir = app_path('Buildora/Resources');
        if (!File::exists($resourceDir)) {
            return false;
        }

        $resourceFiles = File::files($resourceDir);
        $hasResources = count($resourceFiles) > 0;

        if (!$hasResources) {
            return false;
        }

        // Check 3: Config file is published
        $hasConfig = File::exists(config_path('buildora.php'));

        // Check 4: Users table exists and has at least one user
        $hasUsers = false;
        try {
            if (Schema::hasTable('users')) {
                $userClass = config('buildora.user_model', \App\Models\User::class);
                if (class_exists($userClass)) {
                    $hasUsers = $userClass::count() > 0;
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        // Check 5: Permissions table exists (Spatie installed and migrated)
        $hasPermissions = false;
        try {
            $hasPermissions = Schema::hasTable('permissions');
        } catch (\Exception $e) {
            // Silently fail
        }

        // If we have resources + users + permissions table, consider it installed
        // This covers existing installations that don't have the lock file
        if ($hasResources && $hasUsers && $hasPermissions) {
            // Create the lock file for future checks (faster)
            try {
                File::put($lockFile, now()->toDateTimeString());
            } catch (\Exception $e) {
                // Storage might not be writable, ignore
            }
            return true;
        }

        // If we have resources + config + permissions, it's installed (maybe no users yet)
        if ($hasResources && $hasConfig && $hasPermissions) {
            return true;
        }

        return false;
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
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if 2FA is enabled for this user
            if ($this->hasTwoFactorEnabled($user)) {
                $userId = $user->id;
                Auth::logout();
                $request->session()->put('two_factor:user_id', $userId);
                $request->session()->put('two_factor:remember', $remember);

                return redirect()->route('buildora.two-factor.challenge');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('buildora.dashboard'));
        }

        return back()->withErrors([
            'email' => __buildora('The provided credentials are invalid.'),
        ])->withInput($request->only('email'));
    }

    /**
     * Check if the user has two-factor authentication enabled.
     */
    protected function hasTwoFactorEnabled($user): bool
    {
        try {
            $attributes = $user->getAttributes();
            return array_key_exists('two_factor_confirmed_at', $attributes)
                && !empty($attributes['two_factor_confirmed_at']);
        } catch (\Exception $e) {
            return false;
        }
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
