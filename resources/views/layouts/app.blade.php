<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name', 'Zaytouna Academy'))</title>

  <!-- Fonts (Style guide: Montserrat + Playfair Display) -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800|playfair-display:400,600,700&display=swap" rel="stylesheet" />
  @if(app()->getLocale() === 'ar')
  <!-- Arabic font: Cairo supports Arabic + Latin glyphs -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :lang(ar), :lang(ar) * { font-family: 'Cairo', 'Segoe UI', system-ui, sans-serif !important; }
    :lang(ar) .za-heroTitle, :lang(ar) h1, :lang(ar) h2, :lang(ar) h3 { font-family: 'Cairo', system-ui, sans-serif !important; letter-spacing: 0 !important; }
  </style>
  @endif

  @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="za-body">
  @include('layouts.navigation')

  {{-- Flash messages globaux --}}
  @if(session('success') || session('error') || session('warning'))
    <div id="za-flash" style="position:fixed;top:1.2rem;left:50%;transform:translateX(-50%);z-index:9999;min-width:280px;max-width:560px;width:90%;pointer-events:none;">
      @if(session('success'))
        <div class="za-flash-box za-flash-success" onclick="this.parentElement.remove()">
          <span class="za-flash-icon">✅</span>
          <span class="za-flash-text">{{ session('success') }}</span>
        </div>
      @endif
      @if(session('error'))
        <div class="za-flash-box za-flash-error" onclick="this.parentElement.remove()">
          <span class="za-flash-icon">❌</span>
          <span class="za-flash-text">{{ session('error') }}</span>
        </div>
      @endif
      @if(session('warning'))
        <div class="za-flash-box za-flash-warning" onclick="this.parentElement.remove()">
          <span class="za-flash-icon">⚠️</span>
          <span class="za-flash-text">{{ session('warning') }}</span>
        </div>
      @endif
    </div>
    <style>
      .za-flash-box {
        pointer-events: all;
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        border-radius: 16px;
        padding: 1rem 1.2rem;
        font-size: .9rem;
        font-weight: 600;
        box-shadow: 0 12px 40px rgba(0,0,0,.18);
        cursor: pointer;
        animation: zaFlashIn .3s ease, zaFlashOut .4s ease 5.6s forwards;
        border: 1px solid transparent;
      }
      .za-flash-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
      .za-flash-error   { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
      .za-flash-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
      .za-flash-icon    { font-size: 1.1rem; flex-shrink: 0; }
      .za-flash-text    { line-height: 1.5; }
      @keyframes zaFlashIn  { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
      @keyframes zaFlashOut { from { opacity:1; } to { opacity:0; pointer-events:none; } }
    </style>
    <script>
      setTimeout(function(){ var el = document.getElementById('za-flash'); if(el) el.remove(); }, 6000);
    </script>
  @endif

  <main class="za-page">
    @yield('content')
  </main>

  @if (!isset($hideFooter) || !$hideFooter)
  <div data-reveal>
    <x-footer-premium />
  </div>
  @endif

  {{-- IMPORTANT: pour que @push('scripts') fonctionne --}}
  @stack('scripts')
</body>
</html>