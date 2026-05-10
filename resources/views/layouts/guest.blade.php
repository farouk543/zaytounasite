<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Zaytouna Academy') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800|playfair-display:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="za-authBody">
    <div class="za-authBg" aria-hidden="true"></div>

    <div class="za-authWrap">
        <a href="{{ route('home') }}" class="za-authBrand">
            <span class="za-authLogo">ZA</span>
            <span class="za-authName">Zaytouna Academy</span>
        </a>

        <div class="za-authCard">
            {{ $slot }}
        </div>

        <div class="za-authFoot">
            <span>© {{ date('Y') }} Zaytouna Academy</span>
            <span class="za-authSep">•</span>
            <a class="za-authLink" href="{{ route('catalog') }}">Catalog</a>
        </div>
    </div>
</body>
</html>