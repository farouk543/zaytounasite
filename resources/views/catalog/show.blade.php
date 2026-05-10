@extends('layouts.app')

@section('content')
  @php
    $isLive = $course->isPack();

    $isPaid = (bool)($course->is_paid ?? true);
    $price  = number_format(($course->price_cents ?? 0) / 100, 2);

    $title = $course->title_i18n ?? $course->title_display ?? $course->title;
    $desc  = $course->description_i18n ?? $course->description_display ?? '';

    $track   = $course->subject?->level?->track;
    $level   = $course->subject?->level;
    $subject = $course->subject;

    $hasAccess = isset($hasAccess) ? (bool)$hasAccess : (auth()->check() ? auth()->user()->hasActiveEnrollmentForCourse($course->id) : false);
  @endphp

  <div class="bg-gradient-to-b from-white to-gray-50">
    <x-container class="py-10">

      {{-- Breadcrumb --}}
      <div class="text-sm text-gray-600">
        <a href="{{ route('catalog') }}" class="hover:underline">{{ __('ui.catalog') }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900 font-semibold">{{ $title }}</span>
      </div>

      {{-- Hero card --}}
      <div class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
          <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 sm:p-8">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-sky-400 to-emerald-400 opacity-80"></div>

            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
                  {{ $title }}
                </h1>

                <div class="mt-2 flex flex-wrap gap-2 text-sm">
                  @if($track)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                      {{ $track->name_i18n ?? $track->name }}
                    </span>
                  @endif

                  @if($level)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                      {{ $level->name_i18n ?? $level->name }}
                    </span>
                  @endif

                  @if($subject)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                      {{ $subject->name_i18n ?? $subject->name }}
                    </span>
                  @endif

                  <span class="rounded-full {{ $isLive ? 'bg-sky-50 text-sky-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1">
                    {{ $isLive ? __('ui.type_live_pack') : __('ui.type_pdf') }}
                  </span>
                </div>
              </div>

              @if($course->thumbnail_path)
                <img src="{{ asset('storage/' . $course->thumbnail_path) }}"
                     alt=""
                     class="hidden sm:block h-20 w-28 rounded-2xl object-cover border border-gray-200" />
              @endif
            </div>

            <p class="mt-5 text-gray-700 leading-relaxed">
              {{ $desc !== '' ? $desc : __('ui.course.no_description') }}
            </p>

            {{-- Security note / access note --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
              <div class="font-semibold text-gray-900 mb-1">🔒 {{ __('ui.course.secure_access_title') }}</div>
              <div>{{ __('ui.course.secure_access_text') }}</div>

              @if(!empty($course->access_note))
                <div class="mt-2 text-gray-700">
                  <span class="font-semibold">{{ __('ui.note') }}:</span>
                  {{ $course->access_note }}
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Right purchase card --}}
        <div class="lg:col-span-4">
          <div class="sticky top-6 rounded-3xl border border-gray-200 bg-white p-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-600">{{ __('ui.price') }}</div>
              <div class="text-xl font-extrabold text-gray-900">
                @if(!$isPaid)
                  {{ __('ui.free') }}
                @else
                  {{ $price }} {{ $course->currency ?? 'TND' }}
                @endif
              </div>
            </div>

            <div class="mt-3 text-sm text-gray-600">
              ✅ {{ __('ui.course.instant_access') }}<br>
              ✅ {{ __('ui.course.private_visible') }}
            </div>

            <div class="mt-5 space-y-2">
              @if(!$isPaid)
                <a href="{{ $hasAccess ? route('courses.access', $course) : route('courses.show', $course) }}"
                   class="btn-primary za-btnBlock">
                  {{ __('ui.access_now') }}
                </a>
              @else
                @auth
                  @if($hasAccess)
                    <a href="{{ route('courses.access', $course) }}" class="btn-primary za-btnBlock">
                      {{ __('ui.course.access_course') }}
                    </a>
                  @else
                    <form method="POST" action="{{ route('cart.add', $course) }}">
                      @csrf
                      <button class="btn-primary za-btnBlock" type="submit">
                        {{ __('ui.cart.add') }}
                      </button>
                    </form>

                    <a href="{{ route('cart.show') }}" class="btn-outline za-btnBlock">
                      {{ __('ui.cart.view') }}
                    </a>
                  @endif
                @else
                  <a href="{{ route('login') }}" class="btn-primary za-btnBlock">
                    {{ __('ui.login_to_buy') }}
                  </a>

                  <div class="text-xs text-gray-500">
                    {{ __('ui.course.login_required_to_buy') }}
                  </div>
                @endauth
              @endif
            </div>

            @if($isPaid && !$hasAccess)
              <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800">
                <div class="font-semibold">{{ __('ui.course.after_payment_title') }}</div>
                <ul class="mt-1 list-disc pl-5">
                  <li>{{ __('ui.course.after_payment_line1') }}</li>
                  <li>{{ __('ui.course.after_payment_line2') }}</li>
                </ul>
              </div>
            @endif
          </div>
        </div>
      </div>

    </x-container>
    
  </div>
  
@endsection