<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InstitutionType;
use App\Http\Controllers\Controller;
use App\Models\ApiTokenRequest;
use App\Services\ApiTokenRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApiTokenRequestController extends Controller
{
    public function __construct(
        private ApiTokenRequestService $tokenRequests
    ) {}

    public function index(): View
    {
        return view('admin.api-token-requests');
    }

    public function accept(Request $request, ApiTokenRequest $submission): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'category' => ['required', Rule::enum(InstitutionType::class)],
            ]);

            $this->tokenRequests->accept(
                $submission,
                $request->user(),
                InstitutionType::from($validated['category'])
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'API token request accepted and token issued.');
    }

    public function reject(Request $request, ApiTokenRequest $submission): RedirectResponse
    {
        try {
            $this->tokenRequests->reject($submission, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'API token request rejected.');
    }

    public function disable(Request $request, ApiTokenRequest $submission): RedirectResponse
    {
        try {
            $this->tokenRequests->disable($submission, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'API token disabled.');
    }
}
