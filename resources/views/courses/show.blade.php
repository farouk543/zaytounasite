{{-- resources/views/courses/show.blade.php --}}
@extends('layouts.app')

@section('content')
  @php
    use Illuminate\Support\Facades\Storage;

    $isLive = $course->isPack();

    $title = $course->title_i18n ?? $course->title_display ?? $course->title;
    $desc  = $course->description_i18n ?? $course->description_display ?? '';
    $priceQuote = $priceQuote ?? app(\App\Services\PriceResolver::class)->resolve($course);

    $isPaid = (bool)($course->is_paid ?? true);

    $track   = $course->subject?->level?->track;
    $level   = $course->subject?->level;
    $subject = $course->subject;

    // ✅ thumbnail URL (disk public) + robust cleanup + fallback
    $thumbPath = $course->thumbnail_path;
    if ($thumbPath) {
      $thumbPath = preg_replace('#^public/#', '', $thumbPath);
      $thumbPath = ltrim($thumbPath, '/');
    }
    $thumbUrl = $thumbPath
      ? Storage::disk('public')->url($thumbPath)
      : asset('assets/defaults/course-thumb.svg');

    // Controller provides these (CatalogController@show)
    $hasAccess = $hasAccess ?? false;
    $inCart    = $inCart ?? false;
    $relatedCourses = $relatedCourses ?? collect();

    $difficulty = $course->difficulty_level ?? null;
    $difficultyLabel = match($difficulty) {
        'beginner'     => 'Débutant',
        'intermediate' => 'Intermédiaire',
        'advanced'     => 'Avancé',
        default        => null,
    };
    $difficultyColor = match($difficulty) {
        'beginner'     => 'bg-emerald-50 text-emerald-700',
        'intermediate' => 'bg-amber-50 text-amber-700',
        'advanced'     => 'bg-red-50 text-red-700',
        default        => 'bg-gray-100 text-gray-700',
    };
    $enrolledCount = $course->enrollments_count ?? 0;

    // Safe translations (fallback FR)
    $t = fn (string $key, string $fallback) => __($key) === $key ? $fallback : __($key);
  @endphp

  <div class="bg-gradient-to-b from-white to-gray-50">
    <x-container class="py-10">

      {{-- Breadcrumb --}}
      <div class="text-sm text-gray-600">
        <a href="{{ route('catalog') }}" class="hover:underline">{{ $t('ui.catalog', 'Catalogue') }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900 font-semibold">{{ $title }}</span>
      </div>

      {{-- Layout --}}
      <div class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: details --}}
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
                    {{ $isLive ? $t('ui.type_live', 'Live') : $t('ui.type_pdf', 'PDF') }}
                  </span>

                  @if(!$isPaid)
                    <span class="rounded-full bg-emerald-50 text-emerald-700 px-3 py-1">
                      {{ $t('ui.free', 'Gratuit') }}
                    </span>
                  @endif

                  @if($difficultyLabel)
                    <span class="rounded-full {{ $difficultyColor }} px-3 py-1">
                      {{ $difficultyLabel }}
                    </span>
                  @endif

                  @if($course->duration_label)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                      ⏱ {{ $course->duration_label }}
                    </span>
                  @endif

                  @if($enrolledCount > 0)
                    <span class="rounded-full bg-sky-50 text-sky-700 px-3 py-1">
                      👤 {{ number_format($enrolledCount) }} {{ $enrolledCount > 1 ? 'inscrits' : 'inscrit' }}
                    </span>
                  @endif
                </div>
              </div>

              {{-- ✅ Show thumbnail on ALL screen sizes (no hidden sm:block) --}}
              <img
                src="{{ $thumbUrl }}"
                alt=""
                class="h-20 w-28 rounded-2xl object-cover border border-gray-200 bg-gray-50"
                loading="lazy"
              />
            </div>

            <p class="mt-5 text-gray-700 leading-relaxed">
              {{ $desc ?: $t('ui.course_no_description', 'Aucune description pour le moment.') }}
            </p>

            {{-- Security note --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
              <div class="font-semibold text-gray-900 mb-1">
                🔒 {{ $t('ui.security_title', 'Accès sécurisé') }}
              </div>
              <div>
                {{ $t('ui.security_line1', 'Ce cours est accessible uniquement depuis le compte qui l’a acheté.') }}<br>
                {{ $t('ui.security_line2', 'Aucun partage de lien ne donne accès sans inscription active.') }}
              </div>

              @if(!empty($course->access_note))
                <div class="mt-2">
                  <span class="font-semibold">{{ $t('ui.note', 'Note') }} :</span> {{ $course->access_note }}
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Right: purchase --}}
        <div class="lg:col-span-4">
          <div class="sticky top-6 rounded-3xl border border-gray-200 bg-white p-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-600">{{ $t('ui.price', 'Prix') }}</div>
              <div class="text-xl font-extrabold text-gray-900">
                @if(!$isPaid)
                  {{ $t('ui.free', 'Gratuit') }}
                @else
                  {{ $priceQuote->formattedWithHint() }}
                @endif
              </div>
            </div>
            @if($isPaid && $priceQuote->isEstimated)
              <div class="mt-1 text-xs text-gray-500">{{ $t('ui.currency.estimated_note', 'Prix indicatif converti dans votre devise.') }}</div>
            @endif

            <div class="mt-3 text-sm text-gray-600">
              ✅ {{ $t('ui.benefit_instant_access', 'Accès instantané après paiement') }}<br>
              ✅ {{ $t('ui.benefit_private_access', 'Accès privé lié à votre compte') }}
            </div>

            <div class="mt-5 space-y-2">
              @if(!$isPaid)
                {{-- Free --}}
                @auth
                  <a href="{{ route('courses.access', $course) }}" class="btn-primary za-btnBlock">
                    {{ $t('ui.enrollment.access', 'Accéder') }}
                  </a>
                @else
                  <a href="{{ route('login') }}" class="btn-primary za-btnBlock">
                    {{ $t('ui.nav.login', 'Connexion') }}
                  </a>
                @endauth
              @else
                {{-- Paid --}}
                @auth
                  @if($hasAccess)
                    <a href="{{ route('courses.access', $course) }}" class="btn-primary za-btnBlock">
                      {{ $t('ui.access_now', 'Accéder maintenant') }}
                    </a>
                  @else
                    <form method="POST" action="{{ route('cart.add', $course) }}">
                      @csrf
                      <button class="btn-primary za-btnBlock" type="submit" @disabled($inCart)>
                        {{ $inCart ? $t('ui.cart.in_cart', 'Déjà dans le panier') : $t('ui.cart.add_to_cart', 'Ajouter au panier') }}
                      </button>
                    </form>

                    <a href="{{ route('cart.show') }}" class="btn-outline za-btnBlock">
                      {{ $t('ui.cart.view_cart', 'Voir le panier') }}
                    </a>
                  @endif
                @else
                  <a href="{{ route('login') }}" class="btn-primary za-btnBlock">
                    {{ $t('ui.login_to_buy', 'Se connecter pour acheter') }}
                  </a>
                  <div class="text-xs text-gray-500">
                    {{ $t('ui.login_required_to_buy', 'Vous devez être connecté pour payer et accéder au cours.') }}
                  </div>
                @endauth
              @endif
            </div>

            @if($isPaid && !$hasAccess)
              <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800">
                <div class="font-semibold">{{ $t('ui.after_payment', 'Après paiement') }}</div>
                <ul class="mt-1 list-disc pl-5">
                  <li>{{ $t('ui.after_payment_line1', 'Le cours apparaît dans Mon Profil → Mes cours.') }}</li>
                  <li>{{ $t('ui.after_payment_line2', 'Accès uniquement avec votre compte (anti-partage).') }}</li>
                  <li>{{ $t('ui.after_payment_line3', 'L’admin peut fermer l’accès à tout moment.') }}</li>
                </ul>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Related courses --}}
      @if($relatedCourses->isNotEmpty())
        <div class="mt-10">
          <h2 class="text-xl font-extrabold text-gray-900 mb-4">
            Cours similaires
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($relatedCourses as $related)
              <x-course-card :course="$related" />
            @endforeach
          </div>
        </div>
      @endif

    </x-container>
  </div>
@endsection