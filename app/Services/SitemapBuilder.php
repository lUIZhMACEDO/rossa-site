<?php

namespace App\Services;

use App\Models\Study;
use Illuminate\Support\Carbon;

class SitemapBuilder
{
    /**
     * @return array<int, array{loc: string, lastmod?: Carbon, changefreq?: string, priority?: float}>
     */
    public function build(): array
    {
        $urls = [];

        foreach ($this->staticPages() as $page) {
            $urls[] = [
                'loc' => route($page['route']),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        Study::query()
            ->publiclyVisible()
            ->select(['id', 'identifier', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($studies) use (&$urls) {
                foreach ($studies as $study) {
                    $urls[] = [
                        'loc' => route('search.show', $study),
                        'lastmod' => $study->updated_at,
                        'changefreq' => 'yearly',
                        'priority' => 0.6,
                    ];
                }
            });

        return $urls;
    }

    public function isIndexable(?string $routeName): bool
    {
        if (! config('app.allow_indexing') || $routeName === null) {
            return false;
        }

        return in_array($routeName, $this->indexableRouteNames(), true);
    }

    /**
     * Named routes that may be indexed when APP_ALLOW_INDEXING is enabled.
     *
     * @return list<string>
     */
    public function indexableRouteNames(): array
    {
        $static = array_column($this->staticPages(), 'route');

        return array_merge($static, ['search.show']);
    }

    /**
     * @return array<int, array{route: string, priority: float, changefreq: string}>
     */
    private function staticPages(): array
    {
        return [
            ['route' => 'home', 'priority' => 1.0, 'changefreq' => 'monthly'],
            ['route' => 'news.index', 'priority' => 0.7, 'changefreq' => 'weekly'],
            ['route' => 'search.index', 'priority' => 0.9, 'changefreq' => 'weekly'],
            ['route' => 'data-submission', 'priority' => 0.8, 'changefreq' => 'monthly'],
            ['route' => 'depositor-agreement', 'priority' => 0.5, 'changefreq' => 'yearly'],
            ['route' => 'guides.data-ingestion', 'priority' => 0.7, 'changefreq' => 'monthly'],
            ['route' => 'contact', 'priority' => 0.6, 'changefreq' => 'yearly'],
            ['route' => 'about', 'priority' => 0.8, 'changefreq' => 'monthly'],
            ['route' => 'team', 'priority' => 0.7, 'changefreq' => 'monthly'],
            ['route' => 'about.terms-of-use', 'priority' => 0.4, 'changefreq' => 'yearly'],
            ['route' => 'about.mission-statement', 'priority' => 0.7, 'changefreq' => 'monthly'],
            ['route' => 'core-facility.home', 'priority' => 0.8, 'changefreq' => 'monthly'],
            ['route' => 'core-facility.index', 'priority' => 0.8, 'changefreq' => 'weekly'],
            ['route' => 'measurement-parameters', 'priority' => 0.6, 'changefreq' => 'monthly'],
            ['route' => 'download.index', 'priority' => 0.5, 'changefreq' => 'monthly'],
            ['route' => 'api-docs', 'priority' => 0.5, 'changefreq' => 'monthly'],
        ];
    }
}
