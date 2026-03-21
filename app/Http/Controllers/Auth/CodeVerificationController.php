<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CodeVerificationController extends Controller
{
    /**
     * Handle the verification code submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        // If already verified, redirect to dashboard
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Find a valid (non-expired) code for this user
        $verificationCode = EmailVerificationCode::valid()
            ->where('user_id', $request->user()->id)
            ->where('code', $request->code)
            ->first();

        if (!$verificationCode) {
            return back()->withErrors([
                'code' => 'El código es inválido o ha expirado. Por favor, solicita uno nuevo.',
            ]);
        }

        // Mark email as verified
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Delete the used code
        $verificationCode->delete();

        return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
    }
}
