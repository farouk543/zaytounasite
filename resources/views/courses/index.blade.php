{{-- resources/views/courses/index.blade.php (My Courses) --}}
@extends('layouts.app')

@section('content')
  @php
    $fmtPrice = fn($cents) => number_format(((int)($cents ?? 0)) / 100, 2);
  @endphp

  <div class="bg-gradient-to-b from-white to-gray-50">
    <x-container class="py-10">
      <x-section-heading
        :title="__('ui.my_courses.title')"
        :subtitle="__('ui.my_courses.subtitle')"
      >
        <a href="{{ route('catalog') }}" class="text-sm font-semibold text-gray-900 hover:underline">
          {{ __('ui.my_courses.browse_catalog') }}
        </a>
      </x-section-heading>

      <div class="mt-6">
        @if($enrollments->isEmpty())
          <div class="rounded-3xl border border-gray-200 bg-white p-8 text-center">
            <div class="text-lg font-semibold text-gray-900">
              {{ __('ui.my_courses.empty_title') }}
            </div>
            <div class="mt-2 text-sm text-gray-600">
              {{ __('ui.my_courses.empty_text') }}
            </div>

            <a href="{{ route('catalog') }}"
               class="mt-6 inline-flex items-center rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800 transition">
              {{ __('ui.my_courses.explore_catalog') }}
            </a>
          </div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($enrollments as $enrollment)
              @php
                /** @var \App\Models\Course $course */
                $course = $enrollment->course;

                $isLive = $course->isPack();

                // Align with your status + access_ends_at rule
                $active = ($enrollment->status ?? 'active') === 'active'
                          && (empty($enrollment->access_ends_at) || $enrollment->access_ends_at->isFuture());

                $title = $course->title_i18n ?? ($course->title_display ?? $course->title);

                $trackName   = $course->subject?->level?->track?->name_i18n ?? $course->subject?->level?->track?->name;
                $levelName   = $course->subject?->level?->name_i18n ?? $course->subject?->level?->name;
                $subjectName = $course->subject?->name_i18n ?? $course->subject?->name;

                $price = $fmtPrice($course->price_cents);
              @endphp

              <div class="rounded-2xl border border-gray-200 bg-white p-5 hover:shadow-lg transition">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="text-base font-semibold text-gray-900 truncate">
                      {{ $title }}
                    </div>

                    <div class="mt-1 text-sm text-gray-600">
                      @if($trackName) {{ $trackName }} @endif
                      @if($levelName) • {{ $levelName }} @endif
                      @if($subjectName) • {{ $subjectName }} @endif
                    </div>
                  </div>

                  <x-badge :variant="$isLive ? 'info' : 'default'">
                    {{ $isLive ? __('ui.type_live') : __('ui.type_pdf') }}
                  </x-badge>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  @if($active)
                    <x-badge variant="success">{{ __('ui.enrollment.active') }}</x-badge>
                  @else
                    <x-badge variant="warning">{{ __('ui.enrollment.expired') }}</x-badge>
                  @endif

                  <x-badge>{{ $price }} {{ $course->currency ?? 'TND' }}</x-badge>

                  @if(!empty($enrollment->access_ends_at))
                    <x-badge>
                      {{ __('ui.my_courses.ends') }}: {{ $enrollment->access_ends_at->format('Y-m-d') }}
                    </x-badge>
                  @endif
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                  <a href="{{ route('courses.show', $course) }}"
                     class="text-sm font-semibold text-gray-900 hover:underline">
                    {{ __('ui.my_courses.details') }}
                  </a>

                  @if($active)
                    <a href="{{ route('courses.access', $course) }}"
                       class="inline-flex items-center rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 transition">
                      {{ __('ui.enrollment.access') }} →
                    </a>
                  @else
                    <span class="text-sm text-gray-500">{{ __('ui.my_courses.access_locked') }}</span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </x-container>
  </div>
@endsection