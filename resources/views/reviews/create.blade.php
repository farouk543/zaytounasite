@extends('layouts.app')

@section('title', __('reviews.page_title'))
@section('meta_description', __('reviews.page_description'))
{{-- Page utilitaire (lien envoyé aux parents) : inutile dans Google. --}}
@section('robots', 'noindex,follow')

@section('content')
@php
  $field = 'width:100%; padding:11px 14px; border-radius:12px; border:1.5px solid rgba(2,44,32,.12); font-size:14px; outline:none; box-sizing:border-box; font-family:inherit; background:#fff;';
  $label = 'display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#0f172a;';
  $err = 'color:#ef4444; font-size:12px; margin-top:4px;';
  $selectedProgram = old('program', $program ?? 'general');
@endphp

<section class="za-hero" style="min-height:36vh; padding:5.5rem 0 3rem;" aria-label="{{ __('reviews.hero_title') }}">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}'); filter:saturate(.8) brightness(.55);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:640px; padding:36px 30px;" data-reveal>
      <div class="za-badge"><span class="dot"></span><span>{{ __('reviews.hero_badge') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(26px,4vw,44px);">{{ __('reviews.hero_title') }}</h1>
      <p class="za-heroText">{{ __('reviews.hero_subtitle') }}</p>
    </div>
  </div>
</section>

<section class="za-section za-sectionAlt">
  <div class="za-container" style="max-width:720px;">
    @if(session('review_sent'))
      <div class="lux-card" style="padding:32px; text-align:center;" data-reveal>
        <div style="font-size:42px; line-height:1;">🙏</div>
        <h2 class="lux-title" style="margin:14px 0 6px;">{{ __('reviews.sent_title') }}</h2>
        <p class="za-muted">{{ __('reviews.sent_text') }}</p>
        <a class="btn-primary" style="margin-top:18px;" href="{{ route('home') }}">{{ __('reviews.back_home') }}</a>
      </div>
    @else
      <div class="lux-card" style="padding:32px;" data-reveal>
        <div class="lux-title" style="margin-top:0; margin-bottom:20px;">{{ __('reviews.form_title') }}</div>

        <form method="POST" action="{{ route('reviews.store') }}" style="display:grid; gap:16px;" class="zr-form">
          @csrf
          {{-- Piège à robots --}}
          <div aria-hidden="true" style="position:absolute; width:1px; height:1px; overflow:hidden; clip-path:inset(50%); white-space:nowrap;">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>

          <fieldset style="border:0; padding:0; margin:0;">
            <legend style="{{ $label }}">{{ __('reviews.rating') }} <span style="color:#ef4444;">*</span></legend>
            <div class="zr-rating">
              @foreach([5, 4, 3, 2, 1] as $n)
                <input type="radio" id="rating-{{ $n }}" name="rating" value="{{ $n }}" @checked((int) old('rating', 5) === $n) required>
                <label for="rating-{{ $n }}" title="{{ __('reviews.stars', ['n' => $n]) }}"><span class="sr-only">{{ __('reviews.stars', ['n' => $n]) }}</span>★</label>
              @endforeach
            </div>
            @error('rating') <div style="{{ $err }}">{{ $message }}</div> @enderror
          </fieldset>

          <div>
            <label for="quote" style="{{ $label }}">{{ __('reviews.quote') }} <span style="color:#ef4444;">*</span></label>
            <textarea id="quote" name="quote" rows="5" minlength="20" maxlength="1200" required
              placeholder="{{ __('reviews.quote_placeholder') }}" style="{{ $field }} resize:vertical;">{{ old('quote') }}</textarea>
            @error('quote') <div style="{{ $err }}">{{ $message }}</div> @enderror
          </div>

          <div class="zr-two">
            <div>
              <label for="author_name" style="{{ $label }}">{{ __('reviews.name') }} <span style="color:#ef4444;">*</span></label>
              <input id="author_name" type="text" name="author_name" value="{{ old('author_name') }}" required maxlength="80" style="{{ $field }}">
              <small class="za-muted" style="font-size:12px;">{{ __('reviews.name_help') }}</small>
              @error('author_name') <div style="{{ $err }}">{{ $message }}</div> @enderror
            </div>
            <div>
              <label for="author_role" style="{{ $label }}">{{ __('reviews.role') }}</label>
              <input id="author_role" type="text" name="author_role" value="{{ old('author_role') }}" maxlength="120"
                placeholder="{{ __('reviews.role_placeholder') }}" style="{{ $field }}">
            </div>
          </div>

          <div class="zr-two">
            <div>
              <label for="program" style="{{ $label }}">{{ __('reviews.program') }} <span style="color:#ef4444;">*</span></label>
              <select id="program" name="program" required style="{{ $field }}">
                @foreach($programs as $p)
                  <option value="{{ $p }}" @selected($selectedProgram === $p)>{{ __('reviews.programs.'.$p) }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="country" style="{{ $label }}">{{ __('reviews.country') }}</label>
              <select id="country" name="country" style="{{ $field }}">
                <option value="">—</option>
                @foreach($countries as $cc)
                  <option value="{{ $cc }}" @selected(old('country', session('app_country')) === $cc)>{{ \App\Support\Markets::name($cc) }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="zr-two">
            <div>
              <label for="city" style="{{ $label }}">{{ __('reviews.city') }}</label>
              <input id="city" type="text" name="city" value="{{ old('city') }}" maxlength="80" style="{{ $field }}">
            </div>
            <div>
              <label for="contact" style="{{ $label }}">{{ __('reviews.contact') }}</label>
              <input id="contact" type="text" name="contact" value="{{ old('contact') }}" maxlength="150" style="{{ $field }}">
            </div>
          </div>

          <label style="display:flex; gap:10px; align-items:flex-start; font-size:13.5px; color:#334155;">
            <input type="checkbox" name="consent" value="1" required @checked(old('consent')) style="margin-top:3px;">
            <span>{{ __('reviews.consent') }}</span>
          </label>
          @error('consent') <div style="{{ $err }}">{{ $message }}</div> @enderror

          <button type="submit" class="btn-primary za-btnLg" data-track="Envoyer un avis" style="width:100%; justify-content:center; font-size:15px;">
            ⭐ {{ __('reviews.submit') }}
          </button>
        </form>
      </div>
    @endif
  </div>
</section>

<style>
  .zr-two{display:grid; gap:16px; grid-template-columns:1fr 1fr}
  @media (max-width:640px){.zr-two{grid-template-columns:1fr}}
  /* Étoiles cliquables : ordre 5→1 dans le DOM, affichées 1→5 grâce à row-reverse. */
  .zr-rating{display:inline-flex; flex-direction:row-reverse; gap:4px}
  [dir="rtl"] .zr-rating{flex-direction:row-reverse}
  .zr-rating input{position:absolute; opacity:0; width:1px; height:1px}
  .zr-rating label{cursor:pointer; font-size:34px; line-height:1; color:#e5e1d3; transition:color .15s, transform .15s}
  .zr-rating label:hover, .zr-rating label:hover ~ label,
  .zr-rating input:checked ~ label{color:#c9a227}
  .zr-rating input:focus-visible + label{outline:2px solid #0e7454; outline-offset:2px; border-radius:4px}
  .zr-rating label:hover{transform:scale(1.12)}
</style>
@endsection
