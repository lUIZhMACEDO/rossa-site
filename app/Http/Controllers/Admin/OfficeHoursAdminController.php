<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OfficeHoursSessionCancellation;
use App\Models\OfficeHoursSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OfficeHoursAdminController extends Controller
{
    // Show all sessions with registrants
    public function index()
    {
        $sessions = OfficeHoursSession::withTrashed()->withCount([
            'registrations as active_registrations_count' => function ($query) {
                $query->whereNull('cancelled_at');
            },
        ])
            ->orderByDesc('session_date')
            ->paginate(50);

        return view('admin.office-hours.index', compact('sessions'));
    }

    // Show a single session with full registrant details
    public function show(OfficeHoursSession $session)
    {
        $registrations = $session->registrations()
            ->whereNull('cancelled_at')
            ->with('user')
            ->get();

        return view('admin.office-hours.show', compact('session', 'registrations'));
    }

    // Create a new session
    public function store(Request $request)
    {
        $request->validate([
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'teams_link' => 'required|url',
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        OfficeHoursSession::create($request->only([
            'session_date', 'start_time', 'end_time',
            'teams_link', 'assigned_to', 'notes',
        ]));

        return redirect()->route('admin.office-hours.index')
            ->with('success', 'Session created successfully.');
    }

    // Update assigned staff or notes
    public function update(Request $request, OfficeHoursSession $session)
    {
        $request->validate([
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $session->update($request->only(['assigned_to', 'notes']));

        return redirect()->route('admin.office-hours.show', $session)
            ->with('success', 'Session updated.');
    }

    // Cancel a session and notify all registrants
    public function cancel(OfficeHoursSession $session)
    {
        $session->update(['status' => 'cancelled']);

        // Notify all active registrants
        $registrations = $session->registrations()
            ->whereNull('cancelled_at')
            ->with('user')
            ->get();

        foreach ($registrations as $registration) {
            try {
                Mail::to($registration->user->email)
                    ->send(new OfficeHoursSessionCancellation($session, $registration->user));
            } catch (\Throwable $e) {
                Log::error('Failed to send cancellation email to '.$registration->user->email.': '.$e->getMessage());
            }
        }

        return redirect()->route('admin.office-hours.index')
            ->with('success', 'Session cancelled and registrants notified.');
    }
}
