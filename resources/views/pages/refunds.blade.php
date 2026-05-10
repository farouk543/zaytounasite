@extends('layouts.app')

@section('content')

{{-- ===== HERO ===== --}}
<section class="za-hero" style="min-height:38vh; padding:5.5rem 0 3rem;" aria-label="{{ __('ui.pages.refunds.hero_title') }}">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');
              filter:saturate(.6) brightness(.5);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:640px; padding:34px 28px;" data-reveal>
      <div class="za-badge"><span class="dot"></span><span>{{ __('ui.pages.refunds.hero_badge') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(24px,3.5vw,42px);">{{ __('ui.pages.refunds.hero_title') }}</h1>
      <p class="za-heroText" style="font-size:13px; opacity:.8;">{{ now()->format('d/m/Y') }}</p>
    </div>
  </div>
</section>

{{-- ===== CONTENT ===== --}}
<section class="za-section za-sectionAlt">
  <div class="za-container" style="max-width:800px;">

    <div class="lux-card" style="padding:22px 28px; border-left:4px solid var(--gold); margin-bottom:20px;" data-reveal>
      <p class="za-muted" style="line-height:1.8; font-size:14px; margin:0;">
        {{ __('ui.pages.refunds.intro') }}
      </p>
    </div>

    @php
    $articles = [
      [__('ui.pages.refunds.a1_icon'), __('ui.pages.refunds.a1_title'), __('ui.pages.refunds.a1_body')],
      [__('ui.pages.refunds.a2_icon'), __('ui.pages.refunds.a2_title'), __('ui.pages.refunds.a2_body')],
      [__('ui.pages.refunds.a3_icon'), __('ui.pages.refunds.a3_title'), __('ui.pages.refunds.a3_body')],
      [__('ui.pages.refunds.a4_icon'), __('ui.pages.refunds.a4_title'), __('ui.pages.refunds.a4_body')],
      [__('ui.pages.refunds.a5_icon'), __('ui.pages.refunds.a5_title'), __('ui.pages.refunds.a5_body')],
    ];
    @endphp

    <div style="display:grid; gap:14px;">
      @foreach($articles as [$icon, $title, $content])
      <div class="lux-card" style="padding:24px 28px;" data-reveal>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
          <span style="font-size:22px;">{{ $icon }}</span>
          <div style="font-weight:800; font-size:15px; color:var(--emerald-950);">{{ $title }}</div>
        </div>
        <p class="za-muted" style="line-height:1.8; font-size:14px; margin:0;">{{ $content }}</p>
      </div>
      @endforeach
    </div>

    <div class="lux-card" style="padding:20px; text-align:center; margin-top:16px; background:rgba(2,44,32,.03);" data-reveal>
      <p class="za-muted" style="font-size:13px; margin:0 0 12px;">{{ __('ui.pages.refunds.cta_question') }}</p>
      <a class="btn-outline" style="font-size:13px;" href="{{ route('contact') }}">{{ __('ui.pages.refunds.cta_btn') }}</a>
    </div>

  </div>
</section>

@endsection
