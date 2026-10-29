<?php

namespace App\Http\Controllers;

use App\Models\CoreFacility;
use App\Services\CoreFacilityService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CoreFacilityController extends Controller
{
    // public routes
    public function home()
    {
        return view('core-facility.home');
    }

    public function index()
    {
        return view('core-facility.index');
    }

    public function adminIndex()
    {
        if (! Gate::check('manage-facilities')) {
            abort(403);
        }

        return view('admin.core-facility.index');
    }

    public function refresh(Request $request, CoreFacility $facility, CoreFacilityService $cfService)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        try {
            $cfService->refresh($facility);
        } catch (Exception $e) {
            return redirect()->route('core-facility.home')->with('error', $e->getMessage());
        }

        return redirect()->route('core-facility.home')->with('success', 'Your Core Facility has been successfully refreshed.');
    }

    // auth routes
    public function create()
    {
        return view('core-facility.create');
    }
}
