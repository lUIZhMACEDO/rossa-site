<?php

use App\Http\Controllers\Api\StudyController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// API ROUTES
// throttling handle by the 'api' rate limiter defined in AppServiceProvider
// determined by the auth tier - users are also limited on pagination

// Tiers — see InstitutionType for requests/min and per_page limits
//   Public (no token)
//   academic_nonprofit (ability)
//   commercial_for_profit (ability)

// public routes - no token required; optional Bearer auth upgrades rate limits and per_page
Route::middleware('throttle:api')->group(function () {

    // list of approved studies
    Route::get('/studies', [StudyController::class, 'index']);

    // full details on a single study by ROSSA identifier
    Route::get('/studies/{identifier}', [StudyController::class, 'show']);

    // list of genes associated with a study
    Route::get('/studies/{identifier}/genes', [StudyController::class, 'genes']);

    // sub-resources not yet implemented
    Route::get('/studies/{identifier}/animal-models', [StudyController::class, 'animalModels']);
    Route::get('/studies/{identifier}/diseases', [StudyController::class, 'diseases']);
    Route::get('/studies/{identifier}/investigators', [StudyController::class, 'investigators']);
    Route::get('/studies/{identifier}/sample-groups', [StudyController::class, 'sampleGroups']);
    Route::get('/studies/{identifier}/drug-treatments', [StudyController::class, 'drugTreatments']);
    Route::get('/studies/{identifier}/analysis-uploads', [StudyController::class, 'analysisUploads']);
});

// authenticated routes - sanctum token required
Route::middleware(['auth:sanctum', 'active-user', 'throttle:api'])->group(function () {
    Route::get('/user', [UserController::class, 'show']);
});
