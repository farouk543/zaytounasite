@extends('layouts.app')

@section('content')

{{-- ===== HERO ===== --}}
<section class="za-hero" style="min-height:40vh; padding:5.5rem 0 3rem;" aria-label="{{ __('ui.pages.contact.hero_title') }}">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');
              filter:saturate(.8) brightness(.55);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:640px; padding:36px 30px;" data-reveal>
      <div class="za-badge"><span class="dot"></span><span>{{ __('ui.pages.contact.hero_badge') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(26px,4vw,46px);">{{ __('ui.pages.contact.hero_title') }}</h1>
      <p class="za-heroText">{{ __('ui.pages.contact.hero_subtitle') }}</p>
    </div>
  </div>
</section>

{{-- ===== FORM + INFO ===== --}}
<section class="za-section za-sectionAlt">
  <div class="za-container" style="max-width:920px;">

    @if(session('success'))
      <div class="lux-card" style="padding:16px; border-left:4px solid #10b981; margin-bottom:24px; display:flex; gap:12px; align-items:center;">
        <span style="font-size:20px;">✅</span>
        <span style="font-weight:600; color:#065f46;">{{ session('success') }}</span>
      </div>
    @endif

    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:24px;" class="page-contact-grid">

      {{-- Form --}}
      <div class="lux-card" style="padding:32px;" data-reveal>
        <div class="lux-title" style="margin-top:0; margin-bottom:20px;">{{ __('ui.pages.contact.form_title') }}</div>

        <form method="POST" action="{{ route('contact.send') }}" style="display:grid; gap:16px;">
          @csrf

          <div>
            <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#0f172a;">{{ __('ui.pages.contact.form_name') }} <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
              style="width:100%; padding:11px 14px; border-radius:12px; border:1.5px solid rgba(2,44,32,.12); font-size:14px; outline:none; box-sizing:border-box; font-family:inherit;"
              required maxlength="120">
            @error('name') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
          </div>

          <div>
            <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#0f172a;">{{ __('ui.pages.contact.form_email') }} <span style="color:#ef4444;">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}"
              style="width:100%; padding:11px 14px; border-radius:12px; border:1.5px solid rgba(2,44,32,.12); font-size:14px; outline:none; box-sizing:border-box; font-family:inherit;"
              required maxlength="180">
            @error('email') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
          </div>

          <div>
            <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#0f172a;">{{ __('ui.pages.contact.form_subject') }} <span style="color:#ef4444;">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}"
              style="width:100%; padding:11px 14px; border-radius:12px; border:1.5px solid rgba(2,44,32,.12); font-size:14px; outline:none; box-sizing:border-box; font-family:inherit;"
              required maxlength="200">
            @error('subject') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
          </div>

          <div>
            <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#0f172a;">{{ __('ui.pages.contact.form_message') }} <span style="color:#ef4444;">*</span></label>
            <textarea name="message" rows="5"
              style="width:100%; padding:11px 14px; border-radius:12px; border:1.5px solid rgba(2,44,32,.12); font-size:14px; outline:none; box-sizing:border-box; resize:vertical; font-family:inherit;"
              required minlength="10" maxlength="3000">{{ old('message') }}</textarea>
            @error('message') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
          </div>

          <button type="submit" class="btn-primary za-btnLg" style="width:100%; justify-content:center; font-size:15px;">
            {{ __('ui.pages.contact.form_submit') }}
          </button>
        </form>
      </div>

      {{-- Info cards --}}
      <div style="display:grid; gap:16px; align-content:start;" data-reveal>

        <div class="lux-card" style="padding:22px;">
          <div style="font-size:26px; margin-bottom:10px;">📬</div>
          <div class="lux-title" style="font-size:16px; margin-top:0;">{{ __('ui.pages.contact.response_title') }}</div>
          <p class="lux-text" style="margin-top:8px;">{{ __('ui.pages.contact.response_val') }}</p>
        </div>

        <div class="lux-card" style="padding:22px;">
          <div style="font-size:26px; margin-bottom:10px;">🕐</div>
          <div class="lux-title" style="font-size:16px; margin-top:0;">{{ __('ui.pages.contact.hours_title') }}</div>
          <p class="lux-text" style="margin-top:8px;">{{ __('ui.pages.contact.hours_val') }}</p>
        </div>

        <div class="lux-card" style="padding:22px;">
          <div style="font-size:26px; margin-bottom:10px;">📘</div>
          <div class="lux-title" style="font-size:16px; margin-top:0;">FAQ</div>
          <p class="lux-text" style="margin-top:8px; margin-bottom:12px;">{{ __('ui.pages.faq.hero_text') }}</p>
          <a class="btn-outline" style="font-size:13px;" href="{{ route('faq') }}">{{ __('ui.pages.faq.hero_title') }}</a>
        </div>

        <div class="lux-card" style="padding:22px;">
          <div style="font-size:26px; margin-bottom:10px;">📞</div>
          <div class="lux-title" style="font-size:16px; margin-top:0;">{{ __('ui.pages.contact.phone_title') }}</div>
          <p class="lux-text" style="margin-top:8px;">
            <a href="tel:+21627794742" style="color:inherit; text-decoration:none;">{{ __('ui.pages.contact.phone_val') }}</a>
          </p>
        </div>

        <div class="lux-card" style="padding:22px;">
          <div style="font-size:26px; margin-bottom:10px;">📱</div>
          <div class="lux-title" style="font-size:16px; margin-top:0;">{{ __('ui.pages.contact.social_title') }}</div>
          <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
            <a href="https://www.facebook.com/p/Zaytouna-Academy-61574760645948/" target="_blank" rel="noopener noreferrer" class="btn-outline" style="font-size:13px;">Facebook</a>
            <a href="https://www.youtube.com/@ZaytounaAcademy" target="_blank" rel="noopener noreferrer" class="btn-outline" style="font-size:13px;">YouTube</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<style>
@media (max-width:700px) {
  .page-contact-grid { grid-template-columns: 1fr !important; }
}
</style>

@endsection
