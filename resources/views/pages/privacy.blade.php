@extends('layouts.app')

@section('content')

{{-- ===== HERO ===== --}}
<section class="za-hero" style="min-height:38vh; padding:5.5rem 0 3rem;" aria-label="{{ __('ui.pages.privacy.hero_title') }}">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');
              filter:saturate(.6) brightness(.5);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:640px; padding:34px 28px;" data-reveal>
      <div class="za-badge"><span class="dot"></span><span>{{ __('ui.pages.privacy.hero_badge') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(24px,3.5vw,42px);">{{ __('ui.pages.privacy.hero_title') }}</h1>
      <p class="za-heroText" style="font-size:13px; opacity:.8;">{{ now()->format('d/m/Y') }}</p>
    </div>
  </div>
</section>

{{-- ===== CONTENT ===== --}}
<section class="za-section za-sectionAlt">
  <div class="za-container" style="max-width:800px;">

    @php
    $articles = [
      [__('ui.pages.privacy.a1_title'),  __('ui.pages.privacy.a1_body')],
      [__('ui.pages.privacy.a2_title'),  __('ui.pages.privacy.a2_body')],
      [__('ui.pages.privacy.a3_title'),  __('ui.pages.privacy.a3_body')],
      [__('ui.pages.privacy.a4_title'),  __('ui.pages.privacy.a4_body')],
      [__('ui.pages.privacy.a5_title'),  __('ui.pages.privacy.a5_body')],
      [__('ui.pages.privacy.a6_title'),  __('ui.pages.privacy.a6_body')],
      [__('ui.pages.privacy.a7_title'),  __('ui.pages.privacy.a7_body')],
      [__('ui.pages.privacy.a8_title'),  __('ui.pages.privacy.a8_body')],
      [__('ui.pages.privacy.a9_title'),  __('ui.pages.privacy.a9_body')],
      [__('ui.pages.privacy.a10_title'), __('ui.pages.privacy.a10_body')],
    ];
    @endphp

    <div style="display:grid; gap:14px;">
      @foreach($articles as [$title, $content])
      <div class="lux-card" style="padding:24px 28px;" data-reveal>
        <div style="font-weight:800; font-size:15px; color:var(--emerald-950); margin-bottom:10px;">{{ $title }}</div>
        <p class="za-muted" style="line-height:1.8; font-size:14px; margin:0;">{{ $content }}</p>
      </div>
      @endforeach
    </div>

    <div class="lux-card" style="padding:20px; text-align:center; margin-top:16px; background:rgba(2,44,32,.03);" data-reveal>
      <p class="za-muted" style="font-size:13px; margin:0 0 12px;">{{ __('ui.pages.privacy.cta_question') }}</p>
      <a class="btn-outline" style="font-size:13px;" href="{{ route('contact') }}">{{ __('ui.pages.privacy.cta_btn') }}</a>
    </div>

  </div>
</section>

@endsection
