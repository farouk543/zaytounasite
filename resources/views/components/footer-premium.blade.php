@php
  $year = now()->year;
@endphp

<footer class="site-footer">
  <x-container class="py-14">
    <!-- Top -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
      <!-- Brand -->
      <div class="lg:col-span-4">
        <div class="text-2xl font-black footer-title">{{ __('ui.footer.brand') }}</div>
        <p class="mt-3 text-sm footer-text leading-relaxed">
          {{ __('ui.footer.description') }}
        </p>

        <div class="mt-5 flex flex-wrap items-center gap-2">
          <span class="footer-pill">FR</span>
          <span class="footer-pill">AR</span>
          <span class="footer-pill">EN</span>
          <span class="footer-pill">{{ __('ui.footer.international') }}</span>
        </div>

        <div class="mt-6 flex items-center gap-3 text-sm">
          <a href="https://www.facebook.com/p/Zaytouna-Academy-61574760645948/" target="_blank" rel="noopener noreferrer" class="footer-link">{{ __('ui.footer.social.facebook') }}</a>
          <span class="opacity-30">•</span>
          <a href="https://www.youtube.com/@ZaytounaAcademy" target="_blank" rel="noopener noreferrer" class="footer-link">{{ __('ui.footer.social.youtube') }}</a>
        </div>
      </div>

      <!-- Links -->
      <div class="lg:col-span-5 grid grid-cols-2 sm:grid-cols-3 gap-8">
        <div>
          <div class="text-sm font-extrabold footer-title">{{ __('ui.footer.platform.title') }}</div>
          <ul class="mt-4 space-y-2 text-sm">
            <li><a class="footer-link" href="{{ route('home') }}">{{ __('ui.nav.home') }}</a></li>
            <li><a class="footer-link" href="{{ route('catalog') }}">{{ __('ui.nav.catalog') }}</a></li>
            @auth
              <li><a class="footer-link" href="{{ route('my.courses') }}">{{ __('ui.nav.my_courses') }}</a></li>
            @endauth
          </ul>
        </div>

        <div>
          <div class="text-sm font-extrabold footer-title">{{ __('ui.footer.support.title') }}</div>
          <ul class="mt-4 space-y-2 text-sm">
            <li><a class="footer-link" href="{{ route('about') }}">{{ __('ui.footer.support.about') }}</a></li>
            <li><a class="footer-link" href="{{ route('contact') }}">{{ __('ui.footer.support.contact') }}</a></li>
            <li><a class="footer-link" href="{{ route('faq') }}">{{ __('ui.footer.support.faq') }}</a></li>
          </ul>
        </div>

        <div>
          <div class="text-sm font-extrabold footer-title">{{ __('ui.footer.legal.title') }}</div>
          <ul class="mt-4 space-y-2 text-sm">
            <li><a class="footer-link" href="{{ route('terms') }}">{{ __('ui.footer.legal.terms') }}</a></li>
            <li><a class="footer-link" href="{{ route('privacy') }}">{{ __('ui.footer.legal.privacy') }}</a></li>
            <li><a class="footer-link" href="{{ route('refunds') }}">{{ __('ui.footer.legal.refunds') }}</a></li>
          </ul>
        </div>
      </div>

      <!-- Newsletter -->
      <div class="lg:col-span-3">
        <div class="footer-card">
          <div class="text-sm font-extrabold footer-title">{{ __('ui.footer.newsletter.title') }}</div>
          <p class="mt-2 text-sm footer-text">
            {{ __('ui.footer.newsletter.subtitle') }}
          </p>

          <form class="mt-4 flex flex-wrap gap-2" method="POST" action="#">
            @csrf
            <input
              type="email"
              name="email"
              placeholder="{{ __('ui.footer.newsletter.placeholder') }}"
              class="footer-input"
              style="flex:1; min-width:160px;"
            />
            <button type="submit" class="footer-btn" style="white-space:nowrap;">{{ __('ui.footer.newsletter.join') }}</button>
          </form>

          <p class="mt-3 text-xs footer-text">
            {{ __('ui.footer.newsletter.no_spam') }}
          </p>

          <div class="mt-5 flex flex-wrap gap-2 text-xs">
            <span class="footer-pill">{{ __('ui.footer.pills.secure_access') }}</span>
            <span class="footer-pill">{{ __('ui.footer.pills.live_sessions') }}</span>
            <span class="footer-pill">{{ __('ui.footer.pills.certificates') }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom -->
    <div class="mt-12 pt-6 footer-sep flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <div class="text-xs footer-text">
        © {{ $year }} {{ __('ui.footer.brand') }}. {{ __('ui.footer.rights') }}
      </div>

      <div class="text-xs footer-text">
        {{ __('ui.footer.bottom_note') }}
      </div>
    </div>
  </x-container>
</footer>