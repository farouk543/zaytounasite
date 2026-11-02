@php($featured = $featured ?? false)
<a href="{{ $post->url(app()->getLocale()) }}" class="zb-card {{ $featured ? 'zb-card--featured' : '' }}" data-track="Blog : {{ \Illuminate\Support\Str::limit($post->title, 80) }}">
  <div class="zb-cover">
    @if($post->cover_url)
      <img src="{{ $post->cover_url }}" alt="{{ $post->title_display }}" loading="lazy">
    @else
      <div class="zb-coverMark">Zaytouna</div>
    @endif
    @if($post->is_featured)
      <span class="zb-badge">{{ __('blog.featured') }}</span>
    @endif
  </div>
  <div class="zb-body">
    <div class="zb-meta">
      @if($post->category)
        <span class="zb-cat">{{ $post->category->name_display }}</span><span>•</span>
      @endif
      <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('d M Y') }}</time>
      <span>•</span>
      <span>{{ __('blog.min_read', ['min' => $post->reading_minutes]) }}</span>
    </div>
    <h3 class="zb-title">{{ $post->title_display }}</h3>
    <p class="zb-excerpt">{{ $post->excerpt_display }}</p>
    <span class="zb-more">{{ __('blog.read_more') }} →</span>
  </div>
</a>
