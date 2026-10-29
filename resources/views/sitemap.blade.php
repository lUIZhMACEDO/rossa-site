<?= '<'.'?'.'xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @isset($url['lastmod'])
        <lastmod>{{ $url['lastmod']->toAtomString() }}</lastmod>
        @endisset
        @isset($url['changefreq'])
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        @endisset
        @isset($url['priority'])
        <priority>{{ number_format($url['priority'], 1) }}</priority>
        @endisset
    </url>
@endforeach
</urlset>
