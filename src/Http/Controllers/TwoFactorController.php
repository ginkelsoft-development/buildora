<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show 2FA setup page
     */
    public function setup()
    {
        $user = Auth::user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('buildora.profile.edit')
                ->with('info', __buildora('Two-factor authentication is already enabled.'));
        }

        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->two_factor_secret = Crypt::encryptString($secret);
            $user->save();
        } else {
            $secret = Crypt::decryptString($user->two_factor_secret);
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'Buildora'),
            $user->email,
            $secret
        );

        $qrCodeSvg = $this->generateQrCodeSvg($qrCodeUrl);

        return view('buildora::profile.two-factor-setup', [
            'user' => $user,
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Enable 2FA after verifying code
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        $secret = Crypt::decryptString($user->two_factor_secret);

        if (!$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => __buildora('The provided code is invalid.')]);
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = Crypt::encryptString(json_encode($recoveryCodes));
        $user->two_factor_confirmed_at = now();
        $user->save();

        return view('buildora::profile.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->route('buildora.profile.edit')
            ->with('success', __buildora('Two-factor authentication has been disabled.'));
    }

    /**
     * Show 2FA challenge during login
     */
    public function challenge()
    {
        if (!session('two_factor:user_id')) {
            return redirect()->route('buildora.login');
        }

        return view('buildora::auth.two-factor-challenge');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $userId = session('two_factor:user_id');
        if (!$userId) {
            return redirect()->route('buildora.login');
        }

        $userModel = config('buildora.user_model', \App\Models\User::class);
        $user = $userModel::find($userId);
        if (!$user) {
            return redirect()->route('buildora.login');
        }

        // Check if using recovery code
        $code = $request->input('code');
        $recoveryCode = $request->input('recovery_code');
        $isRecovery = $request->boolean('recovery');

        if ($isRecovery && $recoveryCode) {
            // Try recovery code
            $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);

            if (!in_array($recoveryCode, $recoveryCodes)) {
                return back()->withErrors(['recovery_code' => __buildora('The provided recovery code was invalid.')]);
            }

            // Remove used recovery code
            $recoveryCodes = array_values(array_diff($recoveryCodes, [$recoveryCode]));
            $user->two_factor_recovery_codes = Crypt::encryptString(json_encode($recoveryCodes));
            $user->save();
        } else {
            // Try TOTP code
            if (!$code || strlen($code) !== 6) {
                return back()->withErrors(['code' => __buildora('Please enter a valid 6-digit code.')]);
            }

            $secret = Crypt::decryptString($user->two_factor_secret);

            if (!$this->google2fa->verifyKey($secret, $code)) {
                return back()->withErrors(['code' => __buildora('The provided two-factor authentication code was invalid.')]);
            }
        }

        // Clear session and login
        $remember = session('two_factor:remember', false);
        session()->forget(['two_factor:user_id', 'two_factor:remember']);

        Auth::login($user, $remember);
        session()->regenerate();

        return redirect()->intended(route('buildora.dashboard'));
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        if (!$user->two_factor_confirmed_at) {
            return redirect()->route('buildora.profile.edit');
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = Crypt::encryptString(json_encode($recoveryCodes));
        $user->save();

        return view('buildora::profile.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Generate QR code SVG
     */
    protected function generateQrCodeSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(192),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($url);
    }

    /**
     * Generate recovery codes
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }
}
