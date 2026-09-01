@extends('layouts.app')

@section('content')

{{-- ===== HERO ===== --}}
<section class="za-hero" style="min-height:44vh; padding:5.5rem 0 3.5rem;" aria-label="{{ __('ui.pages.about.hero_title') }}">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');
              filter:saturate(.8) brightness(.55);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:680px; padding:38px 32px;" data-reveal>
      <div class="za-badge"><span class="dot"></span><span>{{ __('ui.pages.about.hero_badge') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(28px,4vw,50px);">{{ __('ui.pages.about.hero_title') }}</h1>
      <p class="za-heroText">{{ __('ui.pages.about.hero_subtitle') }}</p>
      <div class="za-heroActions" style="margin-top:18px;">
        <a class="btn-primary za-btnLg" href="{{ route('regimes.index') }}">{{ __('ui.pages.about.btn_regimes') }}</a>
        <a class="btn-outline za-btnLg" href="{{ route('contact') }}">{{ __('ui.pages.about.btn_contact') }}</a>
      </div>
    </div>
  </div>
</section>

{{-- ===== MISSION ===== --}}
<section class="za-section">
  <div class="za-container" style="max-width:860px;">
    <div class="za-sectionHead" data-reveal>
      <div class="pill">{{ __('ui.pages.about.mission_pill') }}</div>
      <h2 class="za-h2">{{ __('ui.pages.about.mission_title') }}</h2>
      <p class="za-muted">{{ __('ui.pages.about.mission_text') }}</p>
    </div>

    <div class="za-grid3" style="margin-top:32px;">
      @foreach([
        ['🎯', __('ui.pages.about.card1_title'), __('ui.pages.about.card1_desc')],
        ['🌍', __('ui.pages.about.card2_title'), __('ui.pages.about.card2_desc')],
        ['🔐', __('ui.pages.about.card3_title'), __('ui.pages.about.card3_desc')],
      ] as [$icon, $title, $desc])
      <div class="lux-card" style="padding:28px; text-align:center;" data-reveal>
        <div style="font-size:34px; margin-bottom:12px;">{{ $icon }}</div>
        <div class="lux-title" style="font-size:17px;">{{ $title }}</div>
        <p class="lux-text">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ===== OFFERINGS ===== --}}
<section class="za-section za-sectionAlt">
  <div class="za-container" style="max-width:960px;">
    <div class="za-sectionHead" data-reveal>
      <div class="pill">{{ __('ui.pages.about.offers_pill') }}</div>
      <h2 class="za-h2">{{ __('ui.pages.about.offers_title') }}</h2>
      <p class="za-muted">{{ __('ui.pages.about.offers_text') }}</p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-top:32px;">
      @foreach([
        ['🇹🇳', __('ui.pages.about.offer_tn_title'), __('ui.pages.about.offer_tn_desc'), route('regimes.tunisia.show')],
        ['🇶🇦', __('ui.pages.about.offer_qa_title'), __('ui.pages.about.offer_qa_desc'), route('regimes.qatar.show')],
        ['🇸🇦', __('ui.pages.about.offer_sa_title'), __('ui.pages.about.offer_sa_desc'), route('regimes.saudi.show')],
        ['📖',   __('ui.pages.about.offer_qr_title'), __('ui.pages.about.offer_qr_desc'), route('regimes.quran.show')],
        ['🇫🇷', __('ui.pages.about.offer_fr_title'), __('ui.pages.about.offer_fr_desc'), route('regimes.france.show')],
      ] as [$flag, $title, $desc, $url])
      <a href="{{ $url }}" class="lux-card" style="padding:24px; text-align:center; text-decoration:none; display:block; transition:transform .2s;" data-reveal>
        <div style="font-size:30px; margin-bottom:10px;">{{ $flag }}</div>
        <div style="font-weight:800; color:#0f172a; margin-bottom:6px;">{{ $title }}</div>
        <p class="za-muted" style="font-size:13px; margin:0;">{{ $desc }}</p>
      </a>
      @endforeach
    </div>
  </div>
</section>

{{-- ===== VALUES ===== --}}
<section class="za-section">
  <div class="za-container" style="max-width:760px;">
    <div class="za-sectionHead" data-reveal>
      <div class="pill">{{ __('ui.pages.about.values_pill') }}</div>
      <h2 class="za-h2">{{ __('ui.pages.about.values_title') }}</h2>
    </div>
    <div style="margin-top:28px; display:grid; gap:14px;">
      @foreach([
        [__('ui.pages.about.val1_title'), __('ui.pages.about.val1_desc')],
        [__('ui.pages.about.val2_title'), __('ui.pages.about.val2_desc')],
        [__('ui.pages.about.val3_title'), __('ui.pages.about.val3_desc')],
        [__('ui.pages.about.val4_title'), __('ui.pages.about.val4_desc')],
      ] as [$val, $desc])
      <div class="lux-card" style="padding:20px 24px; display:flex; align-items:flex-start; gap:16px;" data-reveal>
        <div style="width:10px; height:10px; border-radius:50%; background:#d4b056; margin-top:5px; flex-shrink:0;"></div>
        <div>
          <span style="font-weight:800; color:#0f172a;">{{ $val }} —</span>
          <span class="za-muted"> {{ $desc }}</span>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ===== CTA ===== --}}
<section class="za-cta">
  <div class="za-container" style="padding:60px 18px;">
    <div class="cta-card" data-reveal>
      <div>
        <div class="za-h3">{{ __('ui.pages.about.cta_title') }}</div>
        <p style="color:rgba(255,255,255,.72); margin-top:8px; font-size:15px;">
          {{ __('ui.pages.about.cta_text') }}
        </p>
      </div>
      <div class="za-ctaActions">
        <a class="btn-primary za-btnLg" href="{{ route('regimes.index') }}">{{ __('ui.pages.about.btn_regimes') }}</a>
        <a class="btn-outline za-btnLg" href="{{ route('catalog') }}">{{ __('ui.pages.about.btn_catalog') }}</a>
      </div>
    </div>
  </div>
</section>

@endsection
