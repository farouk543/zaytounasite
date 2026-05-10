@extends('layouts.app')

@section('content')

{{-- ===== HERO ===== --}}
<section class="za-hero" style="min-height:40vh; padding:5.5rem 0 3rem;" aria-label="FAQ">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');
              filter:saturate(.8) brightness(.55);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:640px; padding:36px 30px;" data-reveal>
      <div class="za-badge"><span class="dot"></span><span>{{ __('ui.pages.faq.hero_badge') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(26px,4vw,46px);">{{ __('ui.pages.faq.hero_title') }}</h1>
      <p class="za-heroText">{{ __('ui.pages.faq.hero_text') }}</p>
    </div>
  </div>
</section>

{{-- ===== FAQ CONTENT ===== --}}
<section class="za-section za-sectionAlt">
  <div class="za-container" style="max-width:800px;">

    @php
    $sections = [
      [
        'title' => __('ui.pages.faq.s1_title'),
        'icon' => '🏫',
        'items' => [
          [__('ui.pages.faq.s1_q1'), __('ui.pages.faq.s1_a1')],
          [__('ui.pages.faq.s1_q2'), __('ui.pages.faq.s1_a2')],
          [__('ui.pages.faq.s1_q3'), __('ui.pages.faq.s1_a3')],
        ],
      ],
      [
        'title' => __('ui.pages.faq.s2_title'),
        'icon' => '💳',
        'items' => [
          [__('ui.pages.faq.s2_q1'), __('ui.pages.faq.s2_a1')],
          [__('ui.pages.faq.s2_q2'), __('ui.pages.faq.s2_a2')],
          [__('ui.pages.faq.s2_q3'), __('ui.pages.faq.s2_a3')],
          [__('ui.pages.faq.s2_q4'), __('ui.pages.faq.s2_a4')],
        ],
      ],
      [
        'title' => __('ui.pages.faq.s3_title'),
        'icon' => '🎓',
        'items' => [
          [__('ui.pages.faq.s3_q1'), __('ui.pages.faq.s3_a1')],
          [__('ui.pages.faq.s3_q2'), __('ui.pages.faq.s3_a2')],
          [__('ui.pages.faq.s3_q3'), __('ui.pages.faq.s3_a3')],
          [__('ui.pages.faq.s3_q4'), __('ui.pages.faq.s3_a4')],
        ],
      ],
      [
        'title' => __('ui.pages.faq.s4_title'),
        'icon' => '📖',
        'items' => [
          [__('ui.pages.faq.s4_q1'), __('ui.pages.faq.s4_a1')],
          [__('ui.pages.faq.s4_q2'), __('ui.pages.faq.s4_a2')],
        ],
      ],
      [
        'title' => __('ui.pages.faq.s5_title'),
        'icon' => '🔐',
        'items' => [
          [__('ui.pages.faq.s5_q1'), __('ui.pages.faq.s5_a1')],
          [__('ui.pages.faq.s5_q2'), __('ui.pages.faq.s5_a2')],
          [__('ui.pages.faq.s5_q3'), __('ui.pages.faq.s5_a3')],
        ],
      ],
    ];
    @endphp

    <div style="display:grid; gap:18px;" x-data="{}">

      @foreach($sections as $si => $section)
      <div class="lux-card" style="padding:0; overflow:hidden;" data-reveal>
        <div style="padding:18px 24px; background:rgba(2,44,32,.04); border-bottom:1px solid rgba(2,44,32,.06); display:flex; align-items:center; gap:12px;">
          <span style="font-size:20px;">{{ $section['icon'] }}</span>
          <span style="font-weight:800; font-size:1rem; color:var(--emerald-950);">{{ $section['title'] }}</span>
        </div>

        <div style="padding:0 24px;">
          @foreach($section['items'] as $qi => $item)
          <div style="{{ !$loop->last ? 'border-bottom:1px solid rgba(2,44,32,.05);' : '' }}" x-data="{ open: false }">
            <button type="button" @click="open = !open"
              style="width:100%; text-align:left; padding:16px 0; display:flex; align-items:center; justify-content:space-between; gap:12px; background:none; border:none; cursor:pointer; font-family:inherit; font-size:14px; font-weight:700; color:#0f172a;">
              <span>{{ $item[0] }}</span>
              <svg :style="open ? 'transform:rotate(180deg)' : ''"
                   style="width:16px; height:16px; flex-shrink:0; transition:transform .2s; color:#64748b;"
                   fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div x-show="open" x-cloak style="padding-bottom:16px;">
              <p class="za-muted" style="font-size:14px; line-height:1.8; margin:0;">{{ $item[1] }}</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endforeach

    </div>

    {{-- CTA --}}
    <div class="za-cta" style="border-radius:26px; margin-top:24px; overflow:hidden;" data-reveal>
      <div class="za-container" style="padding:32px 24px;">
        <div class="cta-card" style="padding:24px;">
          <div>
            <div class="za-h3" style="font-size:1.2rem;">{{ __('ui.pages.faq.cta_title') }}</div>
            <p style="color:rgba(255,255,255,.72); margin-top:6px; font-size:14px;">{{ __('ui.pages.faq.cta_text') }}</p>
          </div>
          <a class="btn-primary" href="{{ route('contact') }}">{{ __('ui.pages.faq.cta_btn') }}</a>
        </div>
      </div>
    </div>

  </div>
</section>

@endsection
