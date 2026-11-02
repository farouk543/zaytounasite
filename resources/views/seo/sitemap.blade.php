{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($entries as $entry)
  <url>
    <loc>{{ $entry['loc'] }}</loc>
@foreach($entry['alternates'] as $locale => $href)
    <xhtml:link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}"/>
@endforeach
@if(! empty($entry['alternates']))
    <xhtml:link rel="alternate" hreflang="x-default" href="{{ $entry['loc'] }}"/>
@endif
@if($entry['lastmod'])
    <lastmod>{{ $entry['lastmod'] }}</lastmod>
@endif
    <changefreq>{{ $entry['changefreq'] }}</changefreq>
    <priority>{{ $entry['priority'] }}</priority>
  </url>
@endforeach
</urlset>
