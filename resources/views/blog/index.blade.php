@extends('layouts.app')

@php
  $pageTitle = $category
    ? $category->name_display.' – '.__('blog.nav').' | Zaytouna Academy'
    : __('seo.pages.blog.title');
  $pageDesc = $category && filled($category->description_display)
    ? $category->description_display
    : __('seo.pages.blog.description');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDesc)
{{-- Recherche et pages 2+ : on ne les fait pas indexer (contenu dupliqué). --}}
@if($q !== '' || $posts->currentPage() > 1)
  @section('robots', 'noindex,follow')
@endif

@section('content')
@include('blog._styles')

<section class="za-hero" style="min-height:40vh; padding:5.5rem 0 4.5rem;" aria-label="{{ __('blog.title') }}">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus1.webp') }}'); filter:saturate(.8) brightness(.5);"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>
  <div class="za-container za-heroInner">
    <div class="za-heroCard" style="max-width:720px; padding:36px 30px;">
      <div class="za-badge"><span class="dot"></span><span>{{ __('blog.pill') }}</span></div>
      <h1 class="za-heroTitle" style="font-size:clamp(28px,4vw,48px);">
        {{ $category ? $category->name_display : __('blog.title') }}
      </h1>
      <p class="za-heroText">{{ $category && filled($category->description_display) ? $category->description_display : __('blog.subtitle') }}</p>
    </div>
  </div>
</section>

<section class="za-section" style="padding-top:0;">
  <div class="zb-wrap">
    <div class="zb-toolbar">
      <nav class="zb-cats" aria-label="Catégories">
        <a href="{{ route('blog.index') }}" class="zb-chip {{ ! $category ? 'is-active' : '' }}">{{ __('blog.all') }}</a>
        @foreach($categories as $cat)
          <a href="{{ route('blog.category', $cat->slug) }}" class="zb-chip {{ $category?->id === $cat->id ? 'is-active' : '' }}">
            {{ $cat->name_display }} <small>{{ $cat->posts_count }}</small>
          </a>
        @endforeach
      </nav>

      <form class="zb-search" method="GET" action="{{ $category ? route('blog.category', $category->slug) : route('blog.index') }}" role="search">
        <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('blog.search') }}" aria-label="{{ __('blog.search') }}">
        <button type="submit" class="btn-dark">{{ __('blog.search_button') }}</button>
      </form>
    </div>

    @if($forYou->isNotEmpty())
      @php($cc = array_search($market, config('marketing.markets'), true))
      <section class="zb-forYou" aria-label="{{ __('blog.for_you', ['country' => \App\Support\Markets::name($cc)]) }}">
        <div class="zb-forYouHead">
          <span class="zb-forYouFlag" aria-hidden="true">{!! \App\Support\Markets::flagHtml($cc, 36) !!}</span>
          <div>
            <h2>{{ __('blog.for_you', ['country' => \App\Support\Markets::name($cc)]) }}</h2>
            <p>{{ __('blog.for_you_sub') }}</p>
          </div>
        </div>
        <div class="zb-grid">
          @foreach($forYou as $post)
            @include('blog._card', ['post' => $post])
          @endforeach
        </div>
      </section>
    @endif

    @if($posts->isEmpty())
      <div class="zb-empty">{{ $q !== '' ? __('blog.no_results') : __('blog.empty') }}</div>
    @else
      <div class="zb-grid">
        @foreach($posts as $post)
          @include('blog._card', ['post' => $post, 'featured' => $loop->first && $posts->currentPage() === 1 && $q === '' && $post->is_featured])
        @endforeach
      </div>

      <div class="zb-pagination">{{ $posts->links() }}</div>
    @endif
  </div>
</section>
@endsection
