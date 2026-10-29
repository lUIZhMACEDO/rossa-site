<?php

namespace App\Http\Controllers;

use App\Mail\OfficeHoursAdminNotification;
use App\Mail\OfficeHoursConfirmation;
use App\Models\OfficeHoursRegistration;
use App\Models\OfficeHoursSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OfficeHoursController extends Controller
{
    // Show upcoming sessions
    public function index()
    {
        $sessions = OfficeHoursSession::where('status', 'active')
            ->where('session_date', '>=', now())
            ->orderBy('session_date')
            ->get();

        // Attach whether the current user is registered for each session
        $userRegistrations = [];
        if (Auth::check()) {
            $userRegistrations = OfficeHoursRegistration::where('user_id', Auth::id())
                ->whereNull('cancelled_at')
                ->pluck('session_id')
                ->toArray();
        }

        return view('office-hours.index', compact('sessions', 'userRegistrations'));
    }

    // Register for a session
    public function register(Request $request, OfficeHoursSession $session)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Check session is still active
        if ($session->isCancelled()) {
            return back()->with('error', 'This session has been cancelled.');
        }

        // Check for existing registration (active or previously cancelled)
        $existing = OfficeHoursRegistration::where('session_id', $session->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            if (is_null($existing->cancelled_at)) {
                return back()->with('error', 'You are already registered for this session.');
            }
            // Restore cancelled registration
            $existing->update([
                'cancelled_at' => null,
                'notes' => $request->notes ?? $existing->notes,
            ]);
        } else {
            OfficeHoursRegistration::create([
                'session_id' => $session->id,
                'user_id' => Auth::id(),
                'notes' => $request->notes,
            ]);
        }

        // Send confirmation email to user
        try {
            Mail::to(Auth::user()->email)->send(new OfficeHoursConfirmation($session, Auth::user()));
        } catch (\Throwable $e) {
            Log::error('Failed to send office hours confirmation email: '.$e->getMessage());
        }

        // Send admin notification
        try {
            Mail::to(config('mail.rossa_admin_mass_email'))
                ->send(new OfficeHoursAdminNotification($session, Auth::user()));
        } catch (\Throwable $e) {
            Log::error('Failed to send office hours admin notification: '.$e->getMessage());
        }

        return redirect()->route('office-hours.index')
            ->with('success', 'You are registered! Check your email for the meeting details.');
    }

    // Cancel a registration
    public function cancel(Request $request, OfficeHoursSession $session)
    {
        $registration = OfficeHoursRegistration::where('session_id', $session->id)
            ->where('user_id', Auth::id())
            ->whereNull('cancelled_at')
            ->first();

        if (! $registration) {
            return back()->with('error', 'Registration not found.');
        }

        $registration->update(['cancelled_at' => now()]);

        return redirect()->route('office-hours.index')
            ->with('success', 'Your registration has been cancelled.');
    }
}
