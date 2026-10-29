<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminOntologyController;
use App\Http\Controllers\Admin\ApiTokenRequestController as AdminApiTokenRequestController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\ContactRequestController;
use App\Http\Controllers\Admin\GeneAdminController;
use App\Http\Controllers\Admin\MediaUploadAdminController;
use App\Http\Controllers\AnimalExperimentationController;
use App\Http\Controllers\AnimalModelController;
use App\Http\Controllers\ApiTokenRequestController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChemblController;
use App\Http\Controllers\ComparisonGroupController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CoreFacilityController;
use App\Http\Controllers\DatacenterController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\GeneController;
use App\Http\Controllers\GuidesController;
use App\Http\Controllers\ImpcDexaController;
use App\Http\Controllers\InvestigatorController;
use App\Http\Controllers\JsonIngester;
use App\Http\Controllers\MeasurementParametersController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OfficeHoursController;
use App\Http\Controllers\PhenotypeAnalysisController;
use App\Http\Controllers\PubChemController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SampleGroupController;
use App\Http\Controllers\ScanDatasetsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\StudyExportController;
use App\Http\Controllers\StudyFigureController;
use App\Http\Controllers\StudyMediaUploadController;
use App\Http\Controllers\StudySubmissionController;
use App\Http\Controllers\StudySummaryController;
use App\Http\Controllers\SubjectAreasController;
use App\Http\Controllers\TeamPageController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PublicController::class, 'home'])->name('home');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');

// Meta Routes
Route::get('/robots.txt', [PublicController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');

// Public Pages
Route::get('/data-submission', [PublicController::class, 'dataSubmission'])->name('data-submission');
Route::get('/depositor-agreement', [PublicController::class, 'depositorAgreement'])->name('depositor-agreement');

Route::get('/guides/data-ingestion', [GuidesController::class, 'dataIngestion'])->name('guides.data-ingestion');

// Contact Page
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:2,60')
    ->name('contact.store');

// Grant Resources Page
Route::view('/grant-resources', 'grant-resources')->name('grant-resources');

// About Pages
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/team', [TeamPageController::class, 'index'])->name('team');
Route::get('/terms-of-use', function () {
    return view('about.terms-of-use');
})->name('about.terms-of-use');
Route::get('/mission-statement', [PublicController::class, 'missionStatement'])->name('about.mission-statement');

// Core Facility routes
Route::get('/core-facilities/your-facility', [CoreFacilityController::class, 'home'])->name('core-facility.home');
Route::get('/core-facilities', [CoreFacilityController::class, 'index'])->name('core-facility.index');
Route::get('/core-facility/{facility}/refresh', [CoreFacilityController::class, 'refresh'])->name('core-facility.refresh');
Route::middleware(['auth', 'active-user', 'verified'])->group(function () {
    Route::get('/core-facility/create', [CoreFacilityController::class, 'create'])->name('core-facility.create');
});

// measurement parameters route
Route::get('/measurement-parameters', [MeasurementParametersController::class, 'showMeasurementParameters'])->name('measurement-parameters');

// getting gene info through alliance api
Route::get('/gene/{symbol}', [GeneController::class, 'show']);
Route::match(['get', 'post'], '/gene-search', [GeneController::class, 'search']);

// pubchem api
Route::get('/pubchem', [PubChemController::class, 'index'])->name('pubchem.index');
Route::post('/pubchem', [PubChemController::class, 'submit'])->name('pubchem.submit');
Route::get('/pubchem-search', [PubChemController::class, 'search'])->name('pubchem.search');
Route::get('/pubchem-compound/{cid}', [PubChemController::class, 'showCompound'])->name('pubchem.show');

// Chembl api

// Route::match(['get', 'post'], '/chembl/search', [ChemblController::class, 'search'])->name('chembl.search');

Route::match(['get', 'post'], '/chembl/search', [ChemblController::class, 'search'])->name('chembl.search');
Route::get('/chembl/details/{chemblId}', [ChemblController::class, 'showDetails'])->name('chembl.details');

// download excel template files
Route::get('/download-files', [FileDownloadController::class, 'index'])->name('download.index');

Route::get('/under-construction', function () {
    return view('under-construction');
})->name('under-construction');

// Search Data Public Routes
Route::get('/api-docs', fn () => view('api'))->name('api-docs');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::middleware(['restrict-embargo'])->group(function () {
    Route::get('/search/{study:identifier}', [SearchController::class, 'show'])->name('search.show');
    Route::get('/search/{study:identifier}/data-exploration', [SearchController::class, 'dataExploration'])->name('search.data-exploration');
    Route::get('/search/{study:identifier}/data-download', [SearchController::class, 'dataDownload'])->name('search.data-download');
    Route::get('/search/{study:identifier}/data-download/all', [SearchController::class, 'downloadAll'])->name('search.data-download.all');
    Route::get('/search/{study:identifier}/media-uploads/{media}/download', [SearchController::class, 'downloadMedia'])->name('search.media-uploads.download');
    Route::get('/search/{study:identifier}/figures/{figure}', [SearchController::class, 'showFigure'])->name('search.figures.show');
});
Route::get('/collections/{collection}', [SearchController::class, 'showCollection'])->name('collections.show');

Route::middleware(['auth', 'active-user', 'verified'])->group(function () {

    Route::get('/my-studies', [PublicController::class, 'myStudies'])->name('my-studies');
    Route::delete('/study/{study}', [StudyController::class, 'destroyOwnedStudy'])->name('study.destroy');

    // Study routes
    Route::post('/study', [StudyController::class, 'store'])->name('study.store');

    // middle ware restricts to user, admin, and collections
    Route::middleware(['access-study'])->group(function () {

        // form routes — blocked for non-admins when the study is locked
        Route::middleware(['ensure-study-editable'])->group(function () {
            // Study Information routes
            Route::get('/study/{study}/study-information', [StudyController::class, 'showInformation'])->name('study.study-information');
            Route::post('/study/{study}/study-information', [StudyController::class, 'storeInformation'])->name('study.study-information.store');

            Route::post('/study/{study}/contributors', [StudyController::class, 'createContributorInvitation'])
                ->middleware('throttle:5,60')
                ->name('study.contributors.store');
            Route::delete('/study/{study}/contributors/{user}', [StudyController::class, 'removeContributor'])->name('study.contributors.destroy');
            Route::post('/study/{study}/contributors/invitations/{invitation}/resend', [StudyController::class, 'resendContributorInvitation'])
                ->middleware('throttle:5,60')
                ->name('study.invitations.resend');
            Route::delete('/study/{study}/contributors/invitations/{invitation}', [StudyController::class, 'revokeContributorInvitation'])->name('study.invitations.revoke');

            // HTMX Validation
            Route::post('/study/{study}/validate-doi', [StudyController::class, 'validateDoi'])->name('study.validate-doi');
            Route::post('/study/{study}/validate-pubmed-id', [StudyController::class, 'validatePubmedId'])->name('study.validate-pubmed-id');

            // Subject Areas routes
            Route::get('/study/{study}/subject-areas', [SubjectAreasController::class, 'show'])->name('study.subject-areas');

            // Investigator routes
            Route::get('/study/{study}/investigators', [InvestigatorController::class, 'show'])->name('study.investigators');
            Route::post('/study/{study}/investigators', [InvestigatorController::class, 'store'])->name('study.save-investigators');

            // Animal Experimentation routes
            Route::prefix('study/{study}/animal-experimentation')->name('study.animal-experimentation')->group(function () {
                // setup page
                Route::get('/setup', [AnimalExperimentationController::class, 'showSetup'])->name('.setup');
                Route::post('/setup/save', [AnimalExperimentationController::class, 'saveSetup'])->name('.setup.save');
                Route::patch('/setup/toggle-genetic-modification', [AnimalExperimentationController::class, 'toggleGeneticModification'])->name('.setup.toggle-genetic-modification');

                // animal models
                Route::get('/animal-models', [AnimalModelController::class, 'index'])->name('.animal-models');
                Route::get('/animal-models/create', [AnimalModelController::class, 'create'])->name('.animal-models.create');
                Route::post('/animal-models', [AnimalModelController::class, 'submitAnimalModel'])->name('.animal-models.store');
                Route::get('/animal-models/edit/{animalModel}', [AnimalModelController::class, 'edit'])->name('.animal-models.edit');
                Route::post('/animal-models/edit/{animalModel}', [AnimalModelController::class, 'submitAnimalModel'])->name('.animal-models.update');
                Route::delete('/animal-models/{animalModel}', [AnimalModelController::class, 'destroy'])->name('.animal-models.destroy');
                Route::get('/animal-models/intercross/{animalModel}', [AnimalModelController::class, 'intercross'])->name('.animal-models.intercross');
                Route::post('/animal-models/create-similar/{animalModel}', [AnimalModelController::class, 'createSimilar'])->name('.animal-models.create-similar');

                // further animal experimentation forms
                Route::get('/drug-treatment', [AnimalExperimentationController::class, 'showDrugTreatment'])->name('.drug-treatment');
                Route::get('/drug-treatment/plans/create', [AnimalExperimentationController::class, 'createDrugAdministrationPlan'])->name('.drug-treatment.plan.create');
                Route::get('/drug-treatment/plans/{plan}/edit', [AnimalExperimentationController::class, 'editDrugAdministrationPlan'])->name('.drug-treatment.plan.edit');
                Route::get('/gonadectomy-procedure', [AnimalExperimentationController::class, 'showGonadectomyProcedure'])->name('.gonadectomy-procedure');

                // Other Intervention routes
                Route::get('/other-intervention', [AnimalExperimentationController::class, 'showOtherIntervention'])->name('.other-intervention');
                Route::get('/other-intervention/plans/create', [AnimalExperimentationController::class, 'createOtherInterventionPlan'])->name('.other-intervention.plan.create');
                Route::get('/other-intervention/plans/{plan}/edit', [AnimalExperimentationController::class, 'editOtherInterventionPlan'])->name('.other-intervention.plan.edit');

            });

            // Sample Groups routes
            Route::get('/study/{study}/sample-groups', [SampleGroupController::class, 'show'])->name('study.sample-groups');
            Route::post('/study/{study}/sample-groups', [SampleGroupController::class, 'submit'])->name('study.sample-groups.save');
            Route::get('/study/{study}/sample-groups/edit/{sampleGroup}', [SampleGroupController::class, 'edit'])->name('study.sample-groups.edit');
            Route::post('/study/{study}/sample-groups/edit', [SampleGroupController::class, 'submit'])->name('study.sample-groups.update');
            Route::delete('/study/{study}/sample-groups/delete', [SampleGroupController::class, 'destroy'])->name('study.sample-groups.delete');

            // Comparison Groups
            Route::prefix('study/{study}/comparison-groups')->name('study.comparison-groups')->group(function () {
                Route::get('', [ComparisonGroupController::class, 'index'])->name('.index');
                Route::get('/create', [ComparisonGroupController::class, 'create'])->name('.create');
                Route::post('', [ComparisonGroupController::class, 'store'])->name('.store');
                Route::get('/{comparisonGroup}/edit', [ComparisonGroupController::class, 'edit'])->name('.edit');
                Route::put('/{comparisonGroup}', [ComparisonGroupController::class, 'update'])->name('.update');
                Route::delete('/{comparisonGroup}', [ComparisonGroupController::class, 'destroy'])->name('.destroy');
            });

            // Phenotype Analysis routes
            Route::get('/study/{study}/phenotype-analysis', [PhenotypeAnalysisController::class, 'index'])->name('study.phenotype-analysis');
            Route::get('/study/{study}/phenotype-analysis/upload/{upload_id?}', [PhenotypeAnalysisController::class, 'upload'])->name('study.phenotype-analysis.upload');
            Route::post('/study/{study}/phenotype-analysis', [PhenotypeAnalysisController::class, 'store'])->name('study.phenotype-analysis.store');
            // Step-specific update routes
            Route::post('/study/{study}/phenotype-analysis/upload/{upload_id}/prepare', [PhenotypeAnalysisController::class, 'updatePrepareUpload'])->name('study.phenotype-analysis.update.prepare');
            Route::post('/study/{study}/phenotype-analysis/upload/{upload_id}/map-measurements', [PhenotypeAnalysisController::class, 'updateMeasurementMappings'])->name('study.phenotype-analysis.update.measurement-mappings');
            Route::post('/study/{study}/phenotype-analysis/upload/{upload_id}/group-samples', [PhenotypeAnalysisController::class, 'updateGroupSamples'])->name('study.phenotype-analysis.update.group-samples');
            Route::post('/study/{study}/phenotype-analysis/upload/{upload_id}/finalize', [PhenotypeAnalysisController::class, 'finalizeUpload'])->name('study.phenotype-analysis.update.finalize');
            Route::delete('/study/{study}/phenotype-analysis/upload/{upload_id}', [PhenotypeAnalysisController::class, 'destroy'])->name('study.phenotype-analysis.destroy');

            Route::get('/study/{study}/scan-datasets', [ScanDatasetsController::class, 'index'])->name('study.scan-datasets.index');
            Route::post('/study/{study}/scan-datasets/continue', [ScanDatasetsController::class, 'continue'])->name('study.scan-datasets.continue');

            // Study-level scan datasets
            Route::post('/study/{study}/scan-datasets/media-uploads', [StudyMediaUploadController::class, 'store'])->name('study.media-uploads.store');
            Route::put('/study/{study}/scan-datasets/media-uploads/{media}/chunks/{index}', [StudyMediaUploadController::class, 'storeChunk'])->name('study.media-uploads.chunk');
            Route::post('/study/{study}/scan-datasets/media-uploads/{media}/complete', [StudyMediaUploadController::class, 'complete'])->name('study.media-uploads.complete');
            Route::patch('/study/{study}/scan-datasets/media-uploads/{media}/sample-group', [StudyMediaUploadController::class, 'assignSampleGroup'])->name('study.media-uploads.assign-sample-group');
            Route::patch('/study/{study}/scan-datasets/media-uploads/{media}/user-upload', [StudyMediaUploadController::class, 'updateUserUpload'])->name('study.media-uploads.update-user-upload');
            Route::get('/study/{study}/scan-datasets/media-uploads/{media}/row', [StudyMediaUploadController::class, 'row'])->name('study.media-uploads.row');
            Route::delete('/study/{study}/scan-datasets/media-uploads/{media}', [StudyMediaUploadController::class, 'destroy'])->name('study.media-uploads.destroy');

            Route::post('/study/{study}/scan-datasets/figures', [StudyFigureController::class, 'store'])->name('study.figures.store');
            Route::patch('/study/{study}/scan-datasets/figures/{figure}', [StudyFigureController::class, 'update'])->name('study.figures.update');
            Route::delete('/study/{study}/scan-datasets/figures/{figure}', [StudyFigureController::class, 'destroy'])->name('study.figures.destroy');

            Route::post('/upload/{upload}/download', [UploadController::class, 'download'])->name('upload.download');

            Route::post('/study/{study}/submission/submit', [StudySubmissionController::class, 'submitStudy'])->name('study.submission.submit');
            Route::post('/study/{study}/submission/request-approval', [StudySubmissionController::class, 'requestApproval'])->name('study.submission.request-approval');
        });

        // Large study media — status/download when study is viewable
        Route::get('/study/{study}/scan-datasets/media-uploads/{media}', [StudyMediaUploadController::class, 'show'])->name('study.media-uploads.show');
        Route::get('/study/{study}/media-uploads/{media}/download', [StudyMediaUploadController::class, 'download'])->name('study.media-uploads.download');
        Route::get('/study/{study}/scan-datasets/figures/{figure}', [StudyFigureController::class, 'show'])->name('study.figures.show');

        // Summary / report routes — always accessible when the user can access the study
        Route::get('/study/{study}/summary', [StudySummaryController::class, 'show'])->name('study.summary');
        Route::get('/study/{study}/summary/information', [StudySummaryController::class, 'information'])->name('study.summary.information');
        Route::get('/study/{study}/summary/exploration', [StudySummaryController::class, 'exploration'])->name('study.summary.exploration');
        Route::get('/study/{study}/summary/download', [StudySummaryController::class, 'download'])->name('study.summary.download');
        Route::get('/study/{study}/summary/download/all', [StudySummaryController::class, 'downloadAll'])->name('study.summary.download-all');
        // Route::get('/study/{study}/submit', [StudyController::class, 'submitForApproval'])->name('study.submit-for-approval');
        // Route::get('/study/{study}/unsubmit', [StudyController::class, 'unsubmitForApproval'])->name('study.unsubmit-for-approval');

        // submission page — always accessible (shows current status)
        Route::get('/study/{study}/submission', [StudySubmissionController::class, 'show'])->name('study.submission');

        // exports
        Route::get('/study/{study}/export/stats-csv', [StudyExportController::class, 'statsCsv'])->name('study.export.stats-csv');

    });

    // User Feedback
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Datacenter
    Route::get('/datacenter', [DatacenterController::class, 'index'])->name('datacenter.index');
    Route::get('/datacenter/studies', [DatacenterController::class, 'studies'])->name('datacenter.studies');
    Route::get('/datacenter/study/{study}', [DatacenterController::class, 'study'])->name('datacenter.study');
    Route::get('/datacenter/gene/{gene}', [DatacenterController::class, 'gene'])->name('datacenter.gene');
    Route::get('/datacenter/explore', [DatacenterController::class, 'exploreAnalysis'])->name('datacenter.explore');

    // Datacenter mockup
    Route::get('/data', [DatacenterController::class, 'dataIndex'])->name('data.index');
    Route::get('/browser', [DatacenterController::class, 'browser'])->name('browser');

    Route::get('/datacenter/impc-dexa', [ImpcDexaController::class, 'index'])->name('datacenter.impc-dexa');

    // JSON Ingester
    Route::get('/json-ingester', [JsonIngester::class, 'index'])->name('json-ingester.index');
    Route::post('/json-ingester/ingest', [JsonIngester::class, 'ingest'])->name('json-ingester.ingest');
});

// Personal collection routes - user auth now, methods protected
Route::middleware(['auth', 'active-user', 'verified'])->group(function () {
    Route::get('/my-collections/{collection}/studies', [PublicController::class, 'myCollectionStudies'])
        ->name('my-collections.studies');

    Route::post('/my-collections/{collection}/submit', [PublicController::class, 'submitMyCollection'])
        ->name('my-collections.submit');

    Route::get('/my-collections/{collection}/edit', [PublicController::class, 'editMyCollection'])->name('my-collections.edit');
    Route::put('/my-collections/{collection}', [PublicController::class, 'updateMyCollection'])->name('my-collections.update');
});

// Authentication Routes

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login', [AuthController::class, 'show_login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/login-redirect', [AuthController::class, 'loginToPreviousPage'])->name('login.redirect');
Route::get('/register-redirect', [AuthController::class, 'registerToPreviousPage'])->name('register.redirect');

// Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->middleware('auth')->name('verification.notice');
// Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['auth', 'signed'])->name('verification.verify');
// Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationNotification'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['guest'])->group(function () {

    Route::get('/register', [AuthController::class, 'show_register'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    Route::get('/forgot-password', [AuthController::class, 'show_forgot_password'])->name('forgot-password.show');
    Route::post('/forgot-password', [AuthController::class, 'forgot_password'])->name('forgot-password.submit');

    Route::get('/reset-password/{token}', [AuthController::class, 'show_reset_password'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset_password'])->name('reset-password');
});

Route::middleware(['auth', 'active-user'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'show'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'confirm'])
        ->middleware(['signed'])
        ->name('verification.confirm');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::prefix('account')->name('account.')->group(function () {
        Route::redirect('/', '/account/profile');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/security', [AccountController::class, 'security'])->name('security');
        Route::get('/api', [AccountController::class, 'api'])->name('api');
    });

    Route::get('/study-collaboration-invitations/{invitation}/{token}', [StudyController::class, 'showContributorInvitation'])->middleware('verified')->name('study.invitations.show');
    Route::post('/study-collaboration-invitations/{invitation}/{token}', [StudyController::class, 'respondToContributorInvitation'])->middleware('verified')->name('study.invitations.respond');
});

Route::middleware(['auth', 'active-user', 'verified'])->group(function () {
    Route::post('/api-token-request', [ApiTokenRequestController::class, 'store'])
        ->name('api-token-request.store');
});

Route::middleware(['auth', 'active-user', 'verified', 'can:manage-studies'])->group(function () {
    Route::get('/admin/studies', [StudyController::class, 'showStudies'])->name('admin.studies');
    Route::get('/admin/studies/{id}/edit', [StudyController::class, 'editStudy'])->name('admin.study.edit');
    Route::delete('/admin/studies/{id}/abandoned', [StudyController::class, 'destroyAbandoned'])->name('admin.study.destroy-abandoned');

    Route::get('/admin/ontology', [AdminOntologyController::class, 'adminAnimalOntology'])->name('admin.ontology');
    Route::get('/admin/ontology/animal-lines', [AdminOntologyController::class, 'adminAnimalOntology'])->name('admin.ontology.animal-lines');
    Route::get('/admin/ontology/disease-lines', [AdminOntologyController::class, 'adminDiseaseOntology'])->name('admin.ontology.disease-lines');

    Route::get('/admin/contact-requests', [ContactRequestController::class, 'index'])->name('admin.contact-requests');
    Route::get('/admin/media-uploads', [MediaUploadAdminController::class, 'index'])->name('admin.media-uploads.index');

    Route::post('/admin/studies/{id}/approve', [StudyController::class, 'approveStudy'])->name('admin.study.approve');
    Route::post('/admin/studies/{id}/reject', [StudyController::class, 'rejectStudy'])->name('admin.study.reject');
});

Route::middleware(['auth', 'active-user', 'verified', 'can:manage-feedback'])->group(function () {
    Route::get('/admin/feedback', [FeedbackController::class, 'index'])->name('admin.feedback');
    Route::patch('/feedback/{feedback}/toggle', [FeedbackController::class, 'toggle'])->name('feedback.toggle');
    Route::delete('/feedback/{feedback}/delete', [FeedbackController::class, 'delete'])->name('feedback.delete');
});

Route::middleware(['auth', 'active-user', 'verified', 'can:manage-facilities'])->group(function () {
    Route::get('/admin/core-facilities', [CoreFacilityController::class, 'adminIndex'])->name('admin.core-facilities');
});

Route::middleware(['auth', 'active-user', 'verified', 'admin'])->group(function () {
    Route::resource('/admin/news', Admin\NewsPostController::class)
        ->except(['show'])
        ->parameters(['news' => 'newsPost'])
        ->names('admin.news');

    Route::get('/admin/users', [UsersController::class, 'index'])->name('admin.users');
    Route::post('/admin/users/{id}/toggle-disabled', [UsersController::class, 'toggleDisabled'])->name('admin.user.toggle-disabled');
    Route::get('/admin/users/{id}/roles', [UsersController::class, 'editUserRoles'])->name('admin.user.roles');
    Route::get('/admin/users/{id}/subscriptions', [UsersController::class, 'editUserSubscriptions'])->name('admin.user.subscriptions');

    Route::get('/admin/api-token-requests', [AdminApiTokenRequestController::class, 'index'])->name('admin.api-token-requests');
    Route::post('/admin/api-token-requests/{submission}/accept', [AdminApiTokenRequestController::class, 'accept'])->name('admin.api-token-requests.accept');
    Route::post('/admin/api-token-requests/{submission}/reject', [AdminApiTokenRequestController::class, 'reject'])->name('admin.api-token-requests.reject');
    Route::post('/admin/api-token-requests/{submission}/disable', [AdminApiTokenRequestController::class, 'disable'])->name('admin.api-token-requests.disable');

    Route::get('/admin/embargo', [StudyController::class, 'showEmbargoedStudies'])->name('admin.embargo.index');
    Route::post('/admin/embargo/{study}/resend', [StudyController::class, 'resendEmbargoReminder'])->name('admin.embargo.resend');

    // Gene Admin Routes
    Route::prefix('admin/genes')->name('admin.genes.')->group(function () {
        Route::get('/', [GeneAdminController::class, 'index'])->name('index');
        Route::get('/{gene}', [GeneAdminController::class, 'show'])->name('show');
        Route::post('/test-alliance-id', [GeneAdminController::class, 'testByAllianceId'])->name('test-alliance-id');
        Route::post('/test-symbol-species', [GeneAdminController::class, 'testBySymbolAndSpecies'])->name('test-symbol-species');
        Route::post('/test-mouse-rat', [GeneAdminController::class, 'testMouseOrRatGene'])->name('test-mouse-rat');
        Route::post('/{gene}/refresh', [GeneAdminController::class, 'testRefresh'])->name('test-refresh');
        Route::delete('/{gene}', [GeneAdminController::class, 'destroy'])->name('destroy');
    });

    // Measurement Management
    Route::get('/admin/measurements', [MeasurementParametersController::class, 'adminIndex'])->name('admin.measurements');
    Route::get('/admin/measurements/create', [MeasurementParametersController::class, 'create'])->name('admin.measurements.create');
    Route::post('/admin/measurements', [MeasurementParametersController::class, 'store'])->name('admin.measurements.store');
    Route::get('/admin/measurements/{measurement}/edit', [MeasurementParametersController::class, 'edit'])->name('admin.measurements.edit');
    Route::put('/admin/measurements/{measurement}', [MeasurementParametersController::class, 'update'])->name('admin.measurements.update');
    Route::delete('/admin/measurements/{measurement}', [MeasurementParametersController::class, 'destroy'])->name('admin.measurements.destroy');

    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics');

    // Office Hours Admin Routes
    Route::prefix('admin/office-hours')->name('admin.office-hours.')->group(function () {
        Route::get('/', [Admin\OfficeHoursAdminController::class, 'index'])->name('index');
        Route::get('/{session}', [Admin\OfficeHoursAdminController::class, 'show'])->name('show');
        Route::post('/', [Admin\OfficeHoursAdminController::class, 'store'])->name('store');
        Route::patch('/{session}', [Admin\OfficeHoursAdminController::class, 'update'])->name('update');
        Route::post('/{session}/cancel', [Admin\OfficeHoursAdminController::class, 'cancel'])->name('cancel');
    });

});

// Office Hours User Routes
Route::middleware(['auth', 'active-user', 'verified'])->group(function () {
    Route::get('/office-hours', [OfficeHoursController::class, 'index'])->name('office-hours.index');
    Route::post('/office-hours/{session}/register', [OfficeHoursController::class, 'register'])->name('office-hours.register');
    Route::post('/office-hours/{session}/cancel', [OfficeHoursController::class, 'cancel'])->name('office-hours.cancel');
});
Route::middleware(['auth', 'active-user', 'verified', 'can:manage-collections'])->group(function () {
    Route::get('/admin/collections', [CollectionController::class, 'index'])->name('admin.collections');
    Route::get('/admin/collections/create', [CollectionController::class, 'create'])->name('admin.collections.create');
    Route::post('/admin/collections', [CollectionController::class, 'store'])->name('admin.collections.store');
    Route::get('/admin/collections/{collection}/edit', [CollectionController::class, 'edit'])->name('admin.collections.edit');
    Route::put('/admin/collections/{collection}', [CollectionController::class, 'update'])->name('admin.collections.update');
    Route::delete('/admin/collections/{collection}', [CollectionController::class, 'destroy'])->name('admin.collections.destroy');
    Route::get('/admin/collections/{collection}/studies', [CollectionController::class, 'studies'])->name('admin.collections.studies');
    Route::post('/admin/collections/{collection}/submit', [CollectionController::class, 'submit'])->name('admin.collections.submit');
    Route::post('/admin/collections/{collection}/approve', [CollectionController::class, 'approve'])->name('admin.collections.approve');
    Route::post('/admin/collections/{collection}/approve-studies', [CollectionController::class, 'approveStudies'])->name('admin.collections.approve-studies');
    Route::delete('/admin/collections/{collection}/studies/{study}', [CollectionController::class, 'destroyStudy'])->name('admin.collections.studies.destroy');
});
