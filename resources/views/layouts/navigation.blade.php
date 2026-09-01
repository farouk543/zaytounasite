@php
  $locale = app()->getLocale();
  $currentCurrency = \App\Services\CurrencyService::current();
  $supportedCurrencies = \App\Services\CurrencyService::SUPPORTED;

  $isHome = request()->routeIs('home');
  $isCatalog = request()->routeIs('catalog') || request()->routeIs('courses.*');
  $isMyCourses = request()->routeIs('my.courses') || request()->routeIs('courses.access') || request()->routeIs('courses.pdf');
  $isPublicSummerClub = request()->routeIs('club.ete');
  $isStudentSummerClub = request()->routeIs('student.club-ete.*');
  $isCart = request()->routeIs('cart.*') || request()->routeIs('checkout');

  $cartCount = count(session('cart.items', []));
  $hasActiveSummerClubEnrollment = auth()->check()
    && \Illuminate\Support\Facades\Route::has('student.club-ete.catalogue')
    && \App\Models\SummerClubEnrollment::query()
        ->where('user_id', auth()->id())
        ->where('status', 'active')
        ->where(function ($query) {
            $query->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now());
        })
        ->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        })
        ->whereNotNull('selected_subjects')
        ->whereJsonLength('selected_subjects', '>', 0)
        ->exists();
@endphp

<nav
  x-data="{ open: false }"
  class="site-header"
  id="siteHeader"
  @keydown.escape.window="open = false"
  @click.outside="open = false"
>
  <div class="za-container">
    <div class="za-navRow">
      <!-- Left: Brand -->
      <a href="{{ route('home') }}" class="za-brand">
        <img
          src="{{ asset('images/favicon.png') }}"
          alt="Zaytouna Academy Logo"
          class="za-brandLogo"
        />
        <span class="za-brandText">
          <span class="za-brandTitle">Zaytouna</span>
          <span class="za-brandSub">Academy</span>
        </span>
      </a>

      <!-- Center: Links (DESKTOP only via CSS) -->
      <div class="za-navLinks">
        <a class="nav-link {{ $isHome ? 'is-active' : '' }}" href="{{ route('home') }}">
          {{ __('ui.nav.home') }}
        </a>

        <a class="nav-link {{ $isCatalog ? 'is-active' : '' }}" href="{{ route('catalog') }}">
          {{ __('ui.nav.catalog') }}
        </a>

        <a class="nav-link {{ $isPublicSummerClub ? 'is-active' : '' }}" href="{{ route('club.ete') }}">
          Club d&#8217;&#233;t&#233;
        </a>

        @auth
          <a class="nav-link {{ $isMyCourses ? 'is-active' : '' }}" href="{{ route('my.courses') }}">
            {{ __('ui.nav.my_courses') }}
          </a>

          @if($hasActiveSummerClubEnrollment)
            <a class="nav-link {{ $isStudentSummerClub ? 'is-active' : '' }}" href="{{ route('student.club-ete.catalogue') }}">
              Club d’été
            </a>
          @endif
        @endauth
      </div>

      <!-- Right: Actions -->
      <div class="za-navActions">
        <!-- Language (DESKTOP only via CSS) -->
        <div class="za-lang">
          <a href="{{ route('lang.switch', 'fr') }}" class="za-langLink {{ $locale === 'fr' ? 'is-active' : '' }}">FR</a>
          <a href="{{ route('lang.switch', 'en') }}" class="za-langLink {{ $locale === 'en' ? 'is-active' : '' }}">EN</a>
          <a href="{{ route('lang.switch', 'ar') }}" class="za-langLink {{ $locale === 'ar' ? 'is-active' : '' }}">AR</a>
        </div>

        {{-- Currency (DESKTOP only via CSS, shares .za-lang styling) --}}
        <div class="za-lang za-cur" aria-label="{{ __('ui.currency.label') }}">
          <select
            class="za-curSelect"
            onchange="if(this.value){window.location.href=this.value;}"
            aria-label="{{ __('ui.currency.label') }}"
          >
            @foreach($supportedCurrencies as $cur)
              <option value="{{ route('currency.switch', $cur) }}" @selected($cur === $currentCurrency)>{{ $cur }}</option>
            @endforeach
          </select>
        </div>

        @auth
          {{-- Cart (Desktop) --}}
          <a class="btn-ghost {{ $isCart ? 'is-active' : '' }}"
             href="{{ route('cart.show') }}"
             style="position:relative;">
            <span style="display:inline-flex; align-items:center; gap:8px;">
              <span aria-hidden="true">🛒</span>
              <span>{{ __('ui.cart.label') }}</span>
            </span>

            @if($cartCount > 0)
              <span
                style="position:absolute; top:-8px; right:-8px; background:#f59e0b; color:#111; font-weight:800; font-size:12px; padding:2px 7px; border-radius:999px; line-height:1;"
                aria-label="{{ $cartCount }} items in cart"
              >
                {{ $cartCount }}
              </span>
            @endif
          </a>

          <a class="btn-ghost" href="{{ route('dashboard') }}">{{ __('ui.nav.dashboard') }}</a>

          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="za-userBtn" type="button">
                <span class="za-userName">{{ Auth::user()->name }}</span>
                <svg class="za-userChevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
            </x-slot>

            <x-slot name="content">
              <x-dropdown-link :href="route('profile.edit')">
                {{ __('ui.nav.profile') }}
              </x-dropdown-link>

              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                  onclick="event.preventDefault(); this.closest('form').submit();">
                  {{ __('ui.nav.logout') }}
                </x-dropdown-link>
              </form>
            </x-slot>
          </x-dropdown>
        @else
          <a class="btn-outline" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
          <a class="btn-primary" href="{{ route('register') }}">{{ __('ui.nav.get_started') }}</a>
        @endauth

        <!-- Mobile toggle (MOBILE only via CSS) -->
        <button
          class="za-burger"
          type="button"
          @click="open = !open"
          :aria-expanded="open.toString()"
          aria-controls="mobileNavPanel"
          aria-label="{{ __('ui.nav.menu') }}"
        >
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    <!-- Mobile panel -->
    <div
      id="mobileNavPanel"
      x-show="open"
      x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
      class="mobile-panel"
    >
      <div class="za-mobileCol">
        <a class="mobile-link {{ $isHome ? 'is-active' : '' }}" href="{{ route('home') }}" @click="open=false">
          {{ __('ui.nav.home') }}
        </a>

        <a class="mobile-link {{ $isCatalog ? 'is-active' : '' }}" href="{{ route('catalog') }}" @click="open=false">
          {{ __('ui.nav.catalog') }}
        </a>

        <a class="mobile-link {{ $isPublicSummerClub ? 'is-active' : '' }}" href="{{ route('club.ete') }}" @click="open=false">
          Club d&#8217;&#233;t&#233;
        </a>

        @auth
          <a class="mobile-link {{ $isMyCourses ? 'is-active' : '' }}" href="{{ route('my.courses') }}" @click="open=false">
            {{ __('ui.nav.my_courses') }}
          </a>

          @if($hasActiveSummerClubEnrollment)
            <a class="mobile-link {{ $isStudentSummerClub ? 'is-active' : '' }}" href="{{ route('student.club-ete.catalogue') }}" @click="open=false">
              Club d’été
            </a>
          @endif

          {{-- Cart (Mobile) --}}
          <a class="mobile-link {{ $isCart ? 'is-active' : '' }}" href="{{ route('cart.show') }}" @click="open=false">
            <span style="display:inline-flex; align-items:center; gap:10px;">
              <span aria-hidden="true">🛒</span>
              <span>{{ __('ui.cart.label') ?? 'Panier' }}</span>
              @if($cartCount > 0)
                <span style="background:#f59e0b; color:#111; font-weight:800; font-size:12px; padding:2px 8px; border-radius:999px; line-height:1;">
                  {{ $cartCount }}
                </span>
              @endif
            </span>
          </a>

          <a class="mobile-link" href="{{ route('dashboard') }}" @click="open=false">
            {{ __('ui.nav.dashboard') }}
          </a>
        @endauth

        <div class="za-mobileLang">
          <a class="za-langLink {{ $locale === 'fr' ? 'is-active' : '' }}" href="{{ route('lang.switch', 'fr') }}">FR</a>
          <a class="za-langLink {{ $locale === 'en' ? 'is-active' : '' }}" href="{{ route('lang.switch', 'en') }}">EN</a>
          <a class="za-langLink {{ $locale === 'ar' ? 'is-active' : '' }}" href="{{ route('lang.switch', 'ar') }}">AR</a>
        </div>

        <div class="za-mobileLang za-mobileCur">
          @foreach($supportedCurrencies as $cur)
            <a class="za-langLink {{ $cur === $currentCurrency ? 'is-active' : '' }}" href="{{ route('currency.switch', $cur) }}">{{ $cur }}</a>
          @endforeach
        </div>

        @guest
          <div class="za-mobileActions">
            <a class="btn-outline za-btnBlock" href="{{ route('login') }}" @click="open=false">{{ __('ui.nav.login') }}</a>
            <a class="btn-primary za-btnBlock" href="{{ route('register') }}" @click="open=false">{{ __('ui.nav.get_started') }}</a>
          </div>
        @endguest
      </div>
    </div>
  </div>
</nav>
