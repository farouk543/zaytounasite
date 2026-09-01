@props(['course'])

@php
  use Illuminate\Support\Facades\Storage;

  $type = $course->delivery_type ?? 'course';

  $typeLabel = match ($type) {
      'pack' => __('ui.type_course_pack'),
      'series' => __('ui.type_series'),
      'exam' => __('ui.type_exam'),
      default => __('ui.type_course'),
  };

  $badgeVariant = match ($type) {
      'pack' => 'info',
      'exam' => 'danger',
      'series' => 'warning',
      default => 'default',
  };

  $title = $course->title_i18n ?? ($course->title_display ?? $course->title);

  $level = $course->subject?->branch?->level ?? $course->subject?->level;
  $track = $level?->track ?? $course->subject?->track;
  $branch = $course->subject?->branch;

  $trackName = $track?->name_i18n ?? $track?->name;
  $levelName = $level?->name_i18n ?? $level?->name;
  $branchName = $branch?->name_i18n ?? $branch?->name;
  $subjectName = $course->subject?->name_i18n ?? $course->subject?->name;

  $isPaid = (bool) ($course->is_paid ?? true);
  $priceQuote = app(\App\Services\PriceResolver::class)->resolve($course);
  $isFeatured = (bool) ($course->is_featured ?? false);
  $enrolledCount = $course->enrollments_count ?? null;
  $durationLabel = $course->duration_label ?? null;
  $difficulty = $course->difficulty_level ?? null;
  $difficultyLabel = match($difficulty) {
      'beginner' => __('ui.difficulty.beginner') !== 'ui.difficulty.beginner' ? __('ui.difficulty.beginner') : 'Débutant',
      'intermediate' => __('ui.difficulty.intermediate') !== 'ui.difficulty.intermediate' ? __('ui.difficulty.intermediate') : 'Intermédiaire',
      'advanced' => __('ui.difficulty.advanced') !== 'ui.difficulty.advanced' ? __('ui.difficulty.advanced') : 'Avancé',
      default => null,
  };
  $difficultyColor = match($difficulty) {
      'beginner' => 'text-emerald-700 bg-emerald-50',
      'intermediate' => 'text-amber-700 bg-amber-50',
      'advanced' => 'text-red-700 bg-red-50',
      default => '',
  };

  // Check if already in cart
  $inCart = array_key_exists((string) ($course->id ?? ''), session('cart.items', []));

  $thumbPath = $course->thumbnail_path;

  if ($thumbPath && str_starts_with($thumbPath, 'public/')) {
      $thumbPath = substr($thumbPath, 7);
  }

  $thumbUrl = $thumbPath ? Storage::disk('public')->url($thumbPath) : null;
@endphp

<div class="group relative overflow-hidden rounded-2xl border {{ $isFeatured ? 'border-amber-300 shadow-md shadow-amber-100' : 'border-gray-200' }} bg-white p-5 hover:shadow-lg transition">
  <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-sky-400 to-emerald-400 opacity-80"></div>

  {{-- Featured ribbon --}}
  @if($isFeatured)
    <div class="absolute top-3 right-3 z-10 rounded-full bg-amber-400 px-2 py-0.5 text-xs font-bold text-amber-900">
      ★ Vedette
    </div>
  @endif

  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0 flex-1">
      <a href="{{ route('courses.show', $course) }}"
         class="text-base font-semibold text-gray-900 group-hover:underline truncate block">
        {{ $title }}
      </a>

      <div class="mt-1 text-sm text-gray-600">
        @if($trackName){{ $trackName }}@endif
        @if($levelName) • {{ $levelName }} @endif
        @if($branchName) • {{ $branchName }} @endif
        @if($subjectName) • {{ $subjectName }} @endif
      </div>

      {{-- Meta badges --}}
      <div class="mt-2 flex flex-wrap gap-1.5 text-xs">
        @if($difficultyLabel)
          <span class="rounded-full px-2 py-0.5 font-semibold {{ $difficultyColor }}">
            {{ $difficultyLabel }}
          </span>
        @endif

        @if($durationLabel)
          <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">
            ⏱ {{ $durationLabel }}
          </span>
        @endif

        @if($enrolledCount !== null && $enrolledCount > 0)
          <span class="rounded-full bg-sky-50 px-2 py-0.5 text-sky-700">
            👤 {{ $enrolledCount }} {{ $enrolledCount > 1 ? 'inscrits' : 'inscrit' }}
          </span>
        @endif
      </div>
    </div>

    <div class="flex flex-col items-end gap-2 shrink-0">
      @if($thumbUrl)
        <img
          src="{{ $thumbUrl }}"
          alt=""
          class="h-12 w-16 rounded-xl object-cover border border-gray-200 bg-gray-50"
          loading="lazy"
        />
      @endif

      <x-badge :variant="$badgeVariant">
        {{ $typeLabel }}
      </x-badge>
    </div>
  </div>

  <div class="mt-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
      @if(!$isPaid)
        <x-badge variant="success">{{ __('ui.free') }}</x-badge>
      @else
        <x-badge>{{ $priceQuote->formattedWithHint() }}</x-badge>
      @endif
    </div>

    <a href="{{ route('courses.show', $course) }}"
       class="text-sm font-semibold text-gray-900 group-hover:translate-x-0.5 transition">
      {{ __('ui.view') }} →
    </a>
  </div>

  <div class="mt-4 flex gap-2">
    @if(!$isPaid)
      <a class="btn-outline za-btnBlock" href="{{ route('courses.show', $course) }}">
        Accéder
      </a>
    @else
      @auth
        @if($inCart)
          <a href="{{ route('cart.show') }}" class="btn-outline za-btnBlock">
            ✓ Dans le panier
          </a>
        @else
          <form method="POST" action="{{ route('cart.add', $course) }}" class="w-full">
            @csrf
            <button type="submit" class="btn-primary za-btnBlock">
              Ajouter au panier
            </button>
          </form>
        @endif
      @else
        <a class="btn-primary za-btnBlock" href="{{ route('login') }}">
          Se connecter pour acheter
        </a>
      @endauth
    @endif
  </div>
</div>