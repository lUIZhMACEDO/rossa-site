<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'feedback' => 'required|string|max:10000',
        ]);

        $feedback = Feedback::create([
            'feedback' => $validated['feedback'],
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'previous_url' => url()->previous(),
            'resolved' => false,
        ]);
        $env = config('app.env') == 'production' ? 'PROD' : 'DEV';

        // send slack webhook
        $webhook_url = config('services.slack.notifications.webhook_url');
        if ($webhook_url) {
            $response = Http::post($webhook_url, [
                'text' => 'New feedback submitted!',
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => '*'.$env.': New Feedback* - '.$feedback->previous_url."\n".$feedback->feedback,
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                \Log::error('Failed to send feedback to Slack: '.$response->body());
            }
        }

        return redirect()->back()->with('success', 'Feedback submitted successfully!  Thank you for helping us improve the ROSSA website.');
    }

    public function index(Request $request)
    {
        $status = $request->query('status');

        $feedbacks = Feedback::query()
            ->when($status === 'resolved', function ($query) {
                $query->where('resolved', true);
            })
            ->when($status === 'unresolved', function ($query) {
                $query->where('resolved', false)
                    ->orWhereNull('resolved');
            })
            ->when($request->query('order') === 'asc', function ($query) {
                $query->orderBy('created_at', 'asc');
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate(10)
            ->withQueryString();

        return view('feedback.index', compact('feedbacks', 'status'));
    }

    public function toggle(Feedback $feedback)
    {
        $feedback->update([
            'resolved' => ! $feedback->resolved,
        ]);

        return redirect()->back()->with('success', 'Feedback status updated successfully.');
    }

    public function delete(Feedback $feedback)
    {
        $feedback->delete();

        return redirect()->back()->with('success', 'Feedback deleted successfully.');
    }
}
