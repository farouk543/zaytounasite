{{--
  Témoignages de parents. Usage : @include('partials.testimonials', ['program' => 'tunisia'])
  (program null = accueil : avis mis en avant, tous programmes).
  Rien n'est affiché tant qu'aucun avis n'est publié. Balisage Course + AggregateRating à partir de 3 avis.
--}}
@php
  $program = $program ?? null;
  $reviews = \App\Models\Testimonial::forProgram($program, 6);
  $summary = \App\Models\Testimonial::summary($program);
  $locale = app()->getLocale();
  $avg = $locale === 'fr' ? number_format($summary['average'], 1, ',', '') : number_format($summary['average'], 1);
@endphp

@if($reviews->isNotEmpty())
<section class="za-section zt-section" aria-labelledby="zt-title-{{ $program ?? 'all' }}">
  <div class="za-container">
    <div class="za-sectionHead" data-reveal>
      <div class="pill">{{ __('reviews.pill') }}</div>
      <h2 class="za-h2" id="zt-title-{{ $program ?? 'all' }}">{{ __('reviews.title') }}</h2>
      <p class="za-muted">{{ __('reviews.subtitle') }}</p>
      @if($summary['count'] >= 3)
        <div class="zt-average">
          <span class="zt-stars" aria-hidden="true">{{ str_repeat('★', (int) round($summary['average'])) }}{{ str_repeat('☆', 5 - (int) round($summary['average'])) }}</span>
          <strong>{{ __('reviews.average', ['avg' => $avg, 'count' => $summary['count']]) }}</strong>
        </div>
      @endif
    </div>

    <div class="zt-grid">
      @foreach($reviews as $review)
        <figure class="zt-card" data-reveal>
          <div class="zt-stars" role="img" aria-label="{{ __('reviews.stars', ['n' => $review->rating]) }}">
            {{ str_repeat('★', $review->rating) }}<span class="zt-off">{{ str_repeat('★', 5 - $review->rating) }}</span>
          </div>
          <blockquote class="zt-quote" @if($locale !== 'fr' && blank($review->{'quote_'.$locale})) lang="fr" dir="auto" @endif>
            « {{ $review->quoteFor($locale) }} »
          </blockquote>
          <figcaption class="zt-author">
            <span class="zt-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->author_name, 0, 1)) }}</span>
            <span>
              <strong>{{ $review->author_name }}</strong>
              @if($review->author_role)<small>{{ $review->author_role }}</small>@endif
              <small class="zt-where">
                @if($review->country && \App\Support\Markets::has($review->country)){!! \App\Support\Markets::flagHtml($review->country, 12) !!}@endif
                {{ collect([$review->city, $review->country ? \App\Support\Markets::name($review->country) : null])->filter()->implode(', ') }}
                @if(! $program) · {{ __('reviews.programs.'.$review->program) }}@endif
              </small>
            </span>
          </figcaption>
        </figure>
      @endforeach
    </div>

    <div class="za-center" style="margin-top:22px;">
      <a class="zt-leave" href="{{ route('reviews.create', $program ? ['programme' => $program] : []) }}" data-track="Laisser un avis">⭐ {{ __('reviews.leave') }}</a>
    </div>
  </div>
</section>

@if($program && $summary['count'] >= 3)
  @push('head')
  <script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Course',
    'name' => __('reviews.programs.'.$program).' – Zaytouna Academy',
    'description' => __('reviews.subtitle'),
    'inLanguage' => $locale,
    'provider' => ['@type' => 'Organization', 'name' => 'Zaytouna Academy', 'sameAs' => url('/')],
    'aggregateRating' => [
      '@type' => 'AggregateRating',
      'ratingValue' => $summary['average'],
      'reviewCount' => $summary['count'],
      'bestRating' => 5,
      'worstRating' => 1,
    ],
    'review' => $reviews->map(fn ($r) => [
      '@type' => 'Review',
      'author' => ['@type' => 'Person', 'name' => $r->author_name],
      'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $r->rating, 'bestRating' => 5],
      'reviewBody' => $r->quote,
      'datePublished' => optional($r->received_at ?? $r->created_at)->toDateString(),
    ])->values()->all(),
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
  @endpush
@endif

@once
<style>
  .zt-section{background:linear-gradient(180deg,#f8f6ef 0%,#fff 100%)}
  .zt-average{display:inline-flex;align-items:center;gap:10px;margin-top:12px;padding:8px 16px;border-radius:999px;
    background:#fff;border:1px solid rgba(201,162,39,.35);box-shadow:0 6px 18px rgba(2,44,32,.06);color:#0f172a;font-size:14px}
  .zt-grid{display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));margin-top:26px}
  .zt-card{margin:0;display:flex;flex-direction:column;gap:14px;padding:24px;border-radius:20px;background:#fff;
    border:1px solid rgba(2,44,32,.08);box-shadow:0 12px 30px rgba(2,44,32,.07)}
  .zt-stars{color:#c9a227;font-size:18px;letter-spacing:2px;line-height:1}
  .zt-off{color:#e5e1d3}
  .zt-quote{margin:0;flex:1;color:#1f2937;font-size:15px;line-height:1.7}
  .zt-author{display:flex;align-items:center;gap:12px}
  .zt-author strong{display:block;color:#052e1f;font-size:14px}
  .zt-author small{display:block;color:#64748b;font-size:12.5px;line-height:1.5}
  .zt-where img{vertical-align:-1px;margin-inline-end:4px;border-radius:2px}
  .zt-leave{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:999px;border:1.5px solid rgba(2,44,32,.18);
    background:#fff;color:#052e1f;font-weight:700;font-size:14px;text-decoration:none;transition:border-color .2s,transform .2s}
  .zt-leave:hover{border-color:#c9a227;transform:translateY(-1px)}
  .zt-avatar{flex:none;display:grid;place-items:center;width:42px;height:42px;border-radius:50%;
    background:linear-gradient(135deg,#0e7454,#052e1f);color:#f5d77a;font-weight:800;font-size:17px}
</style>
@endonce
@endif
