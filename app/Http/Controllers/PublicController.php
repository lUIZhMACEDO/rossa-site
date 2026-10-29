<?php

namespace App\Http\Controllers;

use App\Enums\PermissionType;
use App\Models\Collection;
use App\Models\NewsPost;
use App\Models\Study;
use App\Models\User;
use App\Services\SitemapBuilder;
use App\Services\StudyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    public function home()
    {
        $newsPosts = NewsPost::query()
            ->latest('published_at')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('home', compact('newsPosts'));
    }

    public function about()
    {
        return view('about');
    }

    public function missionStatement()
    {
        return view('about.mission-statement');
    }

    public function coreFacility()
    {
        return view('core-facility');
    }

    public function depositorAgreement()
    {
        return view('depositor-agreement');
    }

    public function dataSubmission()
    {
        $studies = Auth::check() ? Auth::user()->studies : collect();

        return view('data-submission', compact('studies'));
    }

    public function myStudies()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $canAccessCollections = $user !== null && (
            $user->hasPermission(PermissionType::MANAGE_COLLECTIONS->value)
            || $user->hasPermission(PermissionType::VIEW_OWN_COLLECTIONS->value)
            || $user->hasPermission(PermissionType::EDIT_OWN_COLLECTIONS->value)
            || $user->hasPermission(PermissionType::SUBMIT_OWN_COLLECTIONS->value)
        );

        // Owned collection studies stay on the collection list. Contributors are
        // not collection members, so studies they contribute to stay here.
        $studies = $user !== null
            ? Study::query()
                ->where(function ($query) use ($user) {
                    $query->where(function ($owned) use ($user) {
                        $owned->where('user_id', $user->id)
                            ->whereNull('collection_id');
                    })->orWhereHas('contributors', fn ($contributors) => $contributors->whereKey($user->id));
                })
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $collections = $canAccessCollections && $user !== null
            ? Collection::visibleToUser($user)
                ->withStudyStatusCounts()
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        return view('my-studies', compact('studies', 'collections'));
    }

    public function myCollectionStudies(Collection $collection)
    {
        abort_unless($collection->canUserView(Auth::user()), 403);

        $studies = $collection->studies()
            ->with('animalModels.gene')
            ->orderBy('id')
            ->get();

        $readiness = app(StudyService::class)
            ->getCollectionIncompleteRequirements($collection);

        return view('my-collection-studies', compact('collection', 'studies', 'readiness'));
    }

    public function submitMyCollection(Collection $collection)
    {
        abort_unless($collection->canUserSubmit(Auth::user()), 403);

        $studyService = app(StudyService::class);
        $errors = $studyService->submitCollection($collection, Auth::user());

        if (! empty($errors)) {
            return redirect()
                ->route('my-collections.studies', $collection)
                ->with('error', $studyService->firstErrorMessage($errors));
        }

        return redirect()
            ->route('my-collections.studies', $collection)
            ->with('success', 'Collection submitted for ROSSA review.');
    }

    public function editMyCollection(Collection $collection)
    {
        abort_unless($collection->canUserView(Auth::user()), 403);

        $canEdit = $collection->canUserEditMetadata(Auth::user());
        $collection->load(['publications', 'investigators']);

        return view('my-collection-edit', compact('collection', 'canEdit'));
    }

    public function updateMyCollection(Request $request, Collection $collection)
    {
        abort_unless($collection->canUserEditMetadata(Auth::user()), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'funding_sources' => 'nullable|string',
            'conflicts' => 'nullable|string',
            'completion_date' => 'nullable|date',
        ]);

        $collection->update($validated);

        return redirect()
            ->route('my-collections.edit', $collection)
            ->with('success', 'Collection updated.');
    }

    public function robots()
    {
        if (config('app.allow_indexing')) {
            return response()->view('robots.allow')->header('Content-Type', 'text/plain');
        } else {
            return response()->view('robots.disallow')->header('Content-Type', 'text/plain');
        }
    }

    public function sitemap(SitemapBuilder $sitemapBuilder)
    {
        if (! config('app.allow_indexing')) {
            abort(404);
        }

        return response()
            ->view('sitemap', ['urls' => $sitemapBuilder->build()])
            ->header('Content-Type', 'text/xml');
    }
}
