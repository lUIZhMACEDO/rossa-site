<?php

namespace App\Providers;

use App\Enums\ApiTokenRequestStatus;
use App\Enums\InstitutionType;
use App\Enums\StudyStatus;
use App\Http\Controllers\StudyNavController;
use App\Models\AnalysisUpload;
use App\Models\ApiTokenRequest;
use App\Models\ComparisonGroup;
use App\Models\Feedback;
use App\Models\SampleGroup;
use App\Models\Study;
use App\Observers\AnalysisUploadObserver;
use App\Observers\ComparisonGroupObserver;
use App\Observers\SampleGroupObserver;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AlpineJS is bundled with livewire, but we don't always use livewire, so include it always.
        // \Livewire\Livewire::forceAssetInjection();
        Paginator::useBootstrapFive();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureScramble();

        // register model observers
        AnalysisUpload::observe(AnalysisUploadObserver::class);
        ComparisonGroup::observe(ComparisonGroupObserver::class);
        SampleGroup::observe(SampleGroupObserver::class);

        $studyNav = new StudyNavController;
        $studyNav->share(request());

        Blade::if('htmx', function () {
            return request()->header('HX-Request') === 'true';
        });

        View::composer(['components.nav', 'components.nav.user-menu'], function ($view) {
            $pendingStudyReviewsCount = 0;
            $pendingApiTokenRequestsCount = 0;
            $unresolvedFeedbackCount = 0;

            if (Auth::check()) {
                if (Auth::user()->can('manage-studies')) {
                    $pendingStudyReviewsCount = Study::query()
                        ->where('status', StudyStatus::PENDING)
                        ->count();
                }

                if (Auth::user()->can('manage-feedback')) {
                    $pendingApiTokenRequestsCount = ApiTokenRequest::query()
                        ->where('status', ApiTokenRequestStatus::Pending)
                        ->count();

                    $unresolvedFeedbackCount = Feedback::query()
                        ->where(function ($query) {
                            $query->where('resolved', false)
                                ->orWhereNull('resolved');
                        })
                        ->count();
                }
            }

            $view->with([
                'pendingStudyReviewsCount' => $pendingStudyReviewsCount,
                'pendingApiTokenRequestsCount' => $pendingApiTokenRequestsCount,
                'unresolvedFeedbackCount' => $unresolvedFeedbackCount,
                'adminNotificationCount' => $pendingStudyReviewsCount + $pendingApiTokenRequestsCount + $unresolvedFeedbackCount,
            ]);
        });

        $this->loadCollectionMacros();
    }

    protected function configureScramble(): void
    {
        // Allow anyone to view the API docs in all environments.
        // RestrictedDocsAccess middleware checks this gate before falling back to a 403.
        Gate::define('viewApiDocs', fn () => true);

        Scramble::routes(function (Route $route) {
            return str_starts_with($route->uri, 'api/');
        });

        // register bearer scheme for components
        // do not apply globally, SanctumSecurityExtension handles for auth:sanctum routes
        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->components->addSecurityScheme(
                'bearerAuth',
                SecurityScheme::http('bearer')
            );
        });
    }

    protected function configureRateLimiting(): void
    {
        // Token abilities match InstitutionType values assigned at token issuance.
        // Public (no token) receives the lowest limit, keyed by IP.
        RateLimiter::for('api', function (Request $request) {
            foreach (InstitutionType::cases() as $type) {
                if ($request->user()?->tokenCan($type->value)) {
                    $requestsPerMinute = $type->requestsPerMinute();

                    return $requestsPerMinute === null
                        ? Limit::none()
                        : Limit::perMinute($requestsPerMinute)->by($request->user()->id);
                }
            }

            return Limit::perMinute(InstitutionType::PUBLIC_REQUESTS_PER_MINUTE)->by($request->ip());
        });
    }

    public function loadCollectionMacros()
    {

        // Filter out the common excel BOM characters
        Collection::macro('filterBom', function () {
            return $this->filter(function ($value) {
                if (is_string($value)) {
                    // Remove UTF-8 BOM bytes
                    $bom = pack('H*', 'EFBBBF');
                    $value = preg_replace("/^$bom/", '', $value);
                }

                return $value;
            });
        });
    }
}
