<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use App\Services\InvestigatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function show_login(Request $request)
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->isDisabled()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been disabled. Please contact support for more information.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function show_register(Request $request, InvestigatorService $investigatorService)
    {
        return view('auth.register', [
            'countries' => Country::orderBy('name')->get(),
            'states' => $investigatorService->getStates(),
        ]);
    }

    public function register(Request $request, InvestigatorService $investigatorService)
    {
        $states = $investigatorService->getStates();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'department' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'country' => ['required', 'exists:countries,code'],
            'state' => ['nullable', 'required_if:country,US', Rule::in(array_keys($states))],
            'institution_type' => ['required', 'in:academic_nonprofit,commercial_for_profit'],
        ], [], [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'department' => 'primary department',
            'organization' => 'primary organization',
            'country' => 'country',
            'state' => 'state',
            'institution_type' => 'institution type',
        ]);

        $country = Country::firstWhere('code', $validated['country']);

        $user = User::create([
            'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department' => $validated['department'],
            'organization' => $validated['organization'],
            'country_id' => $country->id,
            'state' => $validated['country'] === 'US' ? $validated['state'] : null,
            'institution_type' => $validated['institution_type'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        $user->sendEmailVerificationNotification();

        return redirect()->intended('/')->with('success', 'You have successfully registered!  Please check your email to verify your account.');
    }

    public function show_forgot_password(Request $request)
    {
        return view('auth.forgot-password');
    }

    public function forgot_password(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        try {
            $status = $user ? Password::sendResetLink(
                $request->only('email')
            ) : 'The system was not able to identify a valid identity to send a password reset link to.  Please try to register an account.';
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset link: '.$e->getMessage());

            return back()->withErrors(['email' => 'An error occurred while sending the password reset link.']);
        }

        return $status == Password::ResetLinkSent
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function show_reset_password(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();
            }
        );

        return $status == Password::PasswordReset
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function loginToPreviousPage(Request $request)
    {
        Redirect::setIntendedUrl(Session::previousUrl());

        return redirect()->route('login');
    }

    public function registerToPreviousPage(Request $request)
    {
        Redirect::setIntendedUrl(Session::previousUrl());

        return redirect()->route('register.show');
    }
}
