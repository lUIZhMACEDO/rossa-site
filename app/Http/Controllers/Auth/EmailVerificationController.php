<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewRegistration;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('home'))
            : view('auth.verify-email');
    }

    public function show(Request $request, string $id, string $hash)
    {
        if (! $this->signatureMatchesUser($request, $id, $hash)) {
            abort(403);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home'))->with('success', 'Your email is already verified.');
        }

        return view('auth.confirm-email-verify', [
            'verifyUrl' => $request->fullUrl(),
        ]);
    }

    public function confirm(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home'))->with('success', 'Your email is already verified.');
        }

        $user = $request->user();

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            try {
                Mail::to($user->email)->send(new NewRegistration);
            } catch (\Throwable $e) {
                Log::error('Failed to send welcome email after verification: '.$e->getMessage());
            }
        }

        // Preserve the URL the user was trying to reach (e.g. a collaboration invite).
        return redirect()->intended(route('home'))
            ->with('success', 'Your email has been verified. You can now submit studies or request API access.');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'Your email is already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    private function signatureMatchesUser(Request $request, string $id, string $hash): bool
    {
        $user = $request->user();

        if (! $user || (string) $user->getKey() !== $id) {
            return false;
        }

        return hash_equals(sha1($user->getEmailForVerification()), $hash);
    }
}
