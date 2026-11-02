{{--
  Balises SEO communes à toutes les pages publiques.
  Une page peut surcharger :
    @section('title', '…')                @section('meta_description', '…')
    @section('meta_image', url(...))       @section('og_type', 'article')
    @section('hreflang', 'fr,ar')         → langues réellement disponibles (défaut : fr,en,ar)
    @section('robots', 'noindex,follow')
  et ajouter du JSON-LD avec @push('head').
--}}
@php
  $locale = app()->getLocale();
  $siteName = __('seo.site_name');
  $pageTitle = trim(View::getSection('title') ?: $siteName);
  $description = trim(strip_tags(View::getSection('meta_description') ?: __('seo.default_description')));
  $description = \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', html_entity_decode($description)), 160, '…', preserveWords: true);
  $image = View::getSection('meta_image') ?: asset('images/og-default.jpg');
  $ogType = View::getSection('og_type') ?: 'website';

  $baseUrl = url()->current();
  $withLang = fn (string $l) => $l === 'fr' ? $baseUrl : $baseUrl.'?lang='.$l;
  $canonical = View::getSection('canonical') ?: $withLang($locale);
  $hreflangs = array_values(array_filter(array_map('trim', explode(',', View::getSection('hreflang') ?: 'fr,en,ar'))));

  // Pages privées (compte, panier, exercices…) : jamais dans Google.
  $privateRoute = request()->routeIs(
    'cart.*', 'checkout*', 'dashboard', 'profile.*', 'my.courses', 'courses.access', 'courses.pdf',
    'student.*', 'quiz.*', 'exercise.*', 'login', 'register', 'password.*', 'verification.*'
  );
  $robots = View::getSection('robots') ?: ($privateRoute ? 'noindex,nofollow' : 'index,follow,max-image-preview:large');
  $ogLocales = ['fr' => 'fr_FR', 'en' => 'en_US', 'ar' => 'ar_AR'];
@endphp
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
@unless($privateRoute)
@foreach(\App\Support\Hreflang::alternates($baseUrl, $hreflangs) as $code => $href)
<link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}">
@endunless

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('images/logo-gold-512.png') }}">
<meta name="theme-color" content="#062e20">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="{{ $ogLocales[$locale] ?? 'fr_FR' }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
@if(config('services.google.site_verification'))
<meta name="google-site-verification" content="{{ config('services.google.site_verification') }}">
@endif

@if(request()->routeIs('home'))
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'EducationalOrganization',
  'name' => 'Zaytouna Academy',
  'alternateName' => 'أكاديمية الزيتونة',
  'url' => route('home'),
  'logo' => asset('images/logo-gold-512.png'),
  'image' => asset('images/og-default.jpg'),
  'description' => __('seo.default_description'),
  'sameAs' => [
    'https://www.facebook.com/p/Zaytouna-Academy-61574760645948/',
    'https://www.youtube.com/@ZaytounaAcademy',
  ],
  'availableLanguage' => ['fr', 'ar', 'en'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'WebSite',
  'name' => 'Zaytouna Academy',
  'url' => route('home'),
  'potentialAction' => [
    '@type' => 'SearchAction',
    'target' => route('catalog').'?q={search_term_string}',
    'query-input' => 'required name=search_term_string',
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endif
@stack('head')
