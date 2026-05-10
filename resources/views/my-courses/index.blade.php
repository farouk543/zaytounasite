@extends('layouts.app')

@section('content')
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
                $course = $enrollment->course;
                $isLive = $course->isPack();
                $active = method_exists($enrollment, 'isActive') ? $enrollment->isActive() : true;
                $isPack = $course->delivery_type === 'pack';

                $title = $course->title_i18n ?? $course->title_display ?? $course->title;
                $trackName = $course->subject?->level?->track?->name_i18n ?? $course->subject?->level?->track?->name;
                $subjectName = $course->subject?->name_i18n ?? $course->subject?->name;

                $price = number_format(($course->price_cents ?? 0) / 100, 2);

                $thumbUrl = $course->thumbnail_path
                  ? \Storage::disk('public')->url($course->thumbnail_path)
                  : null;

                $packProg = $isPack ? ($packProgressMap[$course->id] ?? null) : null;
              @endphp

              <div class="rounded-2xl border border-gray-200 bg-white p-5 hover:shadow-lg transition">
                <div class="flex items-start gap-4">
                  <div class="shrink-0">
                    <div class="h-16 w-24 rounded-2xl overflow-hidden border border-gray-200 bg-gray-50">
                      @if($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="" class="h-full w-full object-cover" />
                      @else
                        <div class="h-full w-full flex items-center justify-center text-xs text-gray-500">ZA</div>
                      @endif
                    </div>
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900 truncate">
                          {{ $title }}
                        </div>

                        <div class="mt-1 text-sm text-gray-600">
                          @if($trackName) {{ $trackName }} @endif
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

                      @if($enrollment->access_ends_at)
                        <x-badge>
                          {{ __('ui.my_courses.ends') }}: {{ $enrollment->access_ends_at->format('Y-m-d') }}
                        </x-badge>
                      @endif
                    </div>

                    {{-- Pack progression --}}
                    @if($packProg && $packProg['total_items'] > 0)
                      <div class="mt-4">
                        <div class="flex items-center justify-between mb-1">
                          <span class="text-xs text-gray-500 font-medium">
                            Progression du pack
                          </span>
                          <span class="text-xs font-bold text-gray-700">
                            {{ $packProg['completed_items'] }}/{{ $packProg['total_items'] }} complétés
                            ({{ $packProg['percentage'] }}%)
                          </span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                          <div
                            class="h-full rounded-full transition-all"
                            style="width:{{ $packProg['percentage'] }}%;background:{{ $packProg['percentage'] >= 100 ? '#10b981' : ($packProg['percentage'] >= 50 ? '#f59e0b' : '#6366f1') }}"
                          ></div>
                        </div>
                      </div>
                    @endif

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
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </x-container>
  </div>
@endsection