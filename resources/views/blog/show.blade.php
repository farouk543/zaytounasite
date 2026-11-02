@extends('layouts.app')

@php
  $locale = app()->getLocale();
  $available = $post->availableLocales();
  $isTranslated = in_array($locale, $available, true);
  // Langue réellement affichée : celle du visiteur si l'article est traduit, sinon le français.
  $contentLocale = $isTranslated ? $locale : 'fr';
  $title = $isTranslated ? $post->title_display : $post->title;
  $excerpt = $isTranslated ? $post->excerpt_display : ($post->excerpt ?: '');
  $shareUrl = $post->url($contentLocale);
  $authorName = $post->author?->name ?: __('blog.team');
  $article = $post->article($contentLocale);
  $isRtl = $contentLocale === 'ar';
  $publishedAt = $post->published_at ?? $post->created_at;
  $wasUpdated = $post->updated_at && $publishedAt && $post->updated_at->gt($publishedAt->copy()->addDays(2));
@endphp

@section('title', ($isTranslated ? $post->meta_title_display : ($post->meta_title ?: $post->title)).' | Zaytouna Academy')
{{-- Toujours une chaîne : une valeur null ferait ouvrir une section Blade jamais refermée. --}}
@section('meta_description', (string) ($isTranslated ? $post->meta_description_display : ($post->meta_description ?: \Illuminate\Support\Str::limit(trim(strip_tags($post->contentHtml('fr'))), 160))))
@section('og_type', 'article')
@section('hreflang', implode(',', $available ?: ['fr']))
@section('canonical', $shareUrl)
@if($post->cover_url)
  @section('meta_image', $post->cover_url)
@endif
@unless($post->isPublished())
  @section('robots', 'noindex,nofollow')
@endunless

@push('head')
<meta property="article:published_time" content="{{ $publishedAt?->toAtomString() }}">
<meta property="article:modified_time" content="{{ $post->updated_at?->toAtomString() }}">
@if($post->category)<meta property="article:section" content="{{ $post->category->name }}">@endif
<script type="application/ld+json">
{!! json_encode(array_filter([
  '@context' => 'https://schema.org',
  '@type' => 'BlogPosting',
  'headline' => \Illuminate\Support\Str::limit($title, 110, ''),
  'description' => $post->meta_description_display,
  'image' => $post->cover_url ?: asset('images/og-default.jpg'),
  'datePublished' => $publishedAt?->toAtomString(),
  'dateModified' => $post->updated_at?->toAtomString(),
  'inLanguage' => $contentLocale,
  'mainEntityOfPage' => $shareUrl,
  'keywords' => $post->focus_keyword,
  'articleSection' => $post->category?->name,
  // Pays visés : signale à Google l'audience géographique de l'article.
  'spatialCoverage' => collect($post->target_countries ?: [])
      ->map(fn ($cc) => ['@type' => 'Country', 'name' => \App\Support\Markets::name($cc, 'en')])->values()->all() ?: null,
  'audience' => ['@type' => 'EducationalAudience', 'educationalRole' => 'parent'],
  'wordCount' => $post->reading_minutes * 200,
  'author' => $post->author
      ? ['@type' => 'Person', 'name' => $authorName]
      : ['@type' => 'Organization', 'name' => 'Zaytouna Academy', 'url' => route('home')],
  'publisher' => [
    '@type' => 'EducationalOrganization',
    'name' => 'Zaytouna Academy',
    'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-gold-512.png')],
  ],
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => array_values(array_filter([
    ['@type' => 'ListItem', 'position' => 1, 'name' => __('blog.home'), 'item' => route('home')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => __('blog.nav'), 'item' => route('blog.index')],
    $post->category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $post->category->name_display, 'item' => route('blog.category', $post->category->slug)] : null,
    ['@type' => 'ListItem', 'position' => $post->category ? 4 : 3, 'name' => $title],
  ])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@if(count($article['faq']))
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => array_map(fn ($item) => [
    '@type' => 'Question',
    'name' => $item['q'],
    'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($item['a']))],
  ], $article['faq']),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endif
@endpush

@section('content')
@include('blog._styles')

<div class="zb-progress" aria-hidden="true"><span id="zbProgress"></span></div>

{{-- ===== En-tête de l'article ===== --}}
<header class="zb-ahero" @if($isRtl) dir="rtl" @endif>
  <div class="zb-aheroGlow" aria-hidden="true"></div>
  <div class="zb-aheroInner {{ $post->cover_url ? '' : 'is-solo' }}">
    <div class="zb-aheroText">
      <nav class="zb-crumbs" aria-label="Fil d'Ariane">
        <a href="{{ route('home') }}">{{ __('blog.home') }}</a><span>/</span>
        <a href="{{ route('blog.index') }}">{{ __('blog.nav') }}</a>
        @if($post->category)
          <span>/</span><a href="{{ route('blog.category', $post->category->slug) }}">{{ $post->category->name_display }}</a>
        @endif
      </nav>

      @if($post->category)
        <a href="{{ route('blog.category', $post->category->slug) }}" class="zb-aCat">{{ $post->category->name_display }}</a>
      @endif

      <h1 class="zb-aTitle">{{ $title }}</h1>

      @if(! empty($post->target_countries))
        <div class="zb-aAudience">
          <span>{{ __('blog.audience') }}</span>
          @foreach($post->target_countries as $cc)
            <span class="zb-aCountry">{!! \App\Support\Markets::flagHtml($cc, 12) !!} {{ \App\Support\Markets::name($cc, $contentLocale) }}</span>
          @endforeach
        </div>
      @endif

      @if(filled($excerpt))
        <p class="zb-aDek">{{ $excerpt }}</p>
      @endif

      <div class="zb-aMeta">
        <img src="{{ asset('images/logo-gold.webp') }}" alt="" class="zb-aAvatar" width="44" height="44">
        <div>
          <div class="zb-aAuthor">{{ $authorName }}</div>
          <div class="zb-aMetaLine">
            <time datetime="{{ $publishedAt?->toDateString() }}">{{ $publishedAt?->translatedFormat('d F Y') }}</time>
            <span>·</span>
            <span>{{ __('blog.min_read', ['min' => $post->reading_minutes]) }}</span>
            @if($wasUpdated)
              <span>·</span><span>{{ __('blog.updated_on', ['date' => $post->updated_at->translatedFormat('d F Y')]) }}</span>
            @endif
          </div>
        </div>
      </div>
    </div>

    @if($post->cover_url)
      <figure class="zb-aCover">
        <img src="{{ $post->cover_url }}" alt="{{ $title }}" width="1200" height="630" fetchpriority="high">
      </figure>
    @endif
  </div>
</header>

{{-- ===== Corps ===== --}}
<div class="zb-aPage">
<div class="zb-aBody" @if($isRtl) dir="rtl" lang="ar" @elseif($contentLocale !== $locale) dir="ltr" lang="{{ $contentLocale }}" @endif>

  @if(count($article['toc']) >= 2)
    <aside class="zb-toc" aria-label="{{ __('blog.toc') }}">
      <div class="zb-tocBox">
        <div class="zb-tocTitle">{{ __('blog.toc') }}</div>
        <ol>
          @foreach($article['toc'] as $entry)
            <li><a href="#{{ $entry['id'] }}" data-toc="{{ $entry['id'] }}" data-no-track>{{ $entry['text'] }}</a></li>
          @endforeach
          @if(count($article['faq']))
            <li><a href="#faq" data-toc="faq" data-no-track>{{ __('blog.faq_title') }}</a></li>
          @endif
        </ol>
      </div>
    </aside>
  @endif

  <article class="zb-article">
    @unless($post->isPublished())
      <div class="zb-notice">{{ __('blog.draft_notice') }}</div>
    @endunless
    @unless($isTranslated)
      <div class="zb-notice">{{ __('blog.translation_missing') }}</div>
    @endunless

    @if(count($article['toc']) >= 2)
      <details class="zb-tocMobile">
        <summary>{{ __('blog.toc') }}</summary>
        <ol>
          @foreach($article['toc'] as $entry)
            <li><a href="#{{ $entry['id'] }}" data-no-track>{{ $entry['text'] }}</a></li>
          @endforeach
        </ol>
      </details>
    @endif

    <div class="zb-prose">
      {!! str_replace(
        \App\Services\Blog\ArticleRenderer::INLINE_CTA,
        view('blog._inline-cta')->render(),
        $article['html']
      ) !!}
    </div>

    @if(count($article['faq']))
      <section class="zb-faq" id="faq" aria-labelledby="faqTitle">
        <h2 id="faqTitle">{{ __('blog.faq_title') }}</h2>
        @foreach($article['faq'] as $i => $item)
          <details class="zb-faqItem" @if($i === 0) open @endif>
            <summary data-track="FAQ : {{ \Illuminate\Support\Str::limit($item['q'], 80) }}">{{ $item['q'] }}</summary>
            <div class="zb-faqAnswer">{!! $item['a'] !!}</div>
          </details>
        @endforeach
      </section>
    @endif

    <div class="zb-authorBox">
      <img src="{{ asset('images/logo-gold.webp') }}" alt="Zaytouna Academy" width="64" height="64">
      <div>
        <div class="zb-authorName">{{ __('blog.team') }}</div>
        <p>{{ __('blog.author_bio') }}</p>
        <a href="{{ route('about') }}">{{ __('blog.about_us') }} →</a>
      </div>
    </div>

    <div class="zb-share" data-no-track>
      <strong>{{ __('blog.share') }}</strong>
      <a href="https://wa.me/?text={{ urlencode($title.' '.$shareUrl) }}" target="_blank" rel="noopener" class="is-wa">WhatsApp</a>
      <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="is-fb">Facebook</a>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener">LinkedIn</a>
      <a href="https://x.com/intent/post?url={{ urlencode($shareUrl) }}&text={{ urlencode($title) }}" target="_blank" rel="noopener">X</a>
      <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(@js($shareUrl)).then(() => { this.textContent = @js(__('blog.copied')); })">{{ __('blog.copy_link') }}</button>
    </div>

    <aside class="zb-cta">
      <h2>{{ __('blog.cta_title') }}</h2>
      <p>{{ __('blog.cta_text') }}</p>
      <div class="zb-ctaBtns">
        <a class="zb-ctaPrimary" href="{{ route('regimes.index') }}" data-track="Blog CTA fin : cours en direct">{{ __('blog.cta_live') }}</a>
        <a class="zb-ctaGhost" href="{{ route('catalog') }}" data-track="Blog CTA fin : catalogue">{{ __('blog.cta_courses') }}</a>
      </div>
    </aside>
  </article>
</div>

@if($related->isNotEmpty())
  <section class="zb-wrap zb-related">
    <h2>{{ __('blog.related') }}</h2>
    <div class="zb-grid">
      @foreach($related as $item)
        @include('blog._card', ['post' => $item])
      @endforeach
    </div>
  </section>
@endif

<div class="zb-wrap" style="padding:36px 16px 70px; text-align:center;">
  <a href="{{ route('blog.index') }}" class="btn-dark">{{ $locale === 'ar' ? '→' : '←' }} {{ __('blog.back') }}</a>
</div>
</div>{{-- /.zb-aPage --}}

@push('scripts')
<script>
(function () {
  var article = document.querySelector('.zb-prose');
  var bar = document.getElementById('zbProgress');
  if (!article) return;

  // Barre de progression + statistiques de lecture (25 / 50 / 75 / 100 %).
  var sent = {};
  var token = @json(csrf_token());
  var endpoint = @json(route('analytics.click'));
  function onScroll() {
    var rect = article.getBoundingClientRect();
    var read = Math.min(1, Math.max(0, (window.innerHeight * 0.6 - rect.top) / rect.height));
    if (bar) bar.style.width = (read * 100).toFixed(1) + '%';
    if (!navigator.sendBeacon) return;
    [25, 50, 75, 100].forEach(function (step) {
      if (read * 100 >= step && !sent[step]) {
        sent[step] = true;
        var fd = new FormData();
        fd.append('_token', token);
        fd.append('p', location.pathname);
        fd.append('l', 'Lecture article ' + step + ' %');
        fd.append('k', 'cta');
        navigator.sendBeacon(endpoint, fd);
      }
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Sommaire : surligne la section en cours de lecture.
  var links = document.querySelectorAll('[data-toc]');
  if (!links.length || !('IntersectionObserver' in window)) return;
  var targets = Array.prototype.map.call(links, function (a) { return document.getElementById(a.dataset.toc); }).filter(Boolean);
  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      links.forEach(function (a) { a.classList.toggle('is-active', a.dataset.toc === entry.target.id); });
    });
  }, { rootMargin: '-100px 0px -65% 0px' });
  targets.forEach(function (t) { observer.observe(t); });
})();
</script>
@endpush
@endsection
