@extends('layouts.app')

@section('content')
  @php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $tr = fn (string $key, string $fallback) => __($key) !== $key ? __($key) : $fallback;

    $typeLabels = [
        'interactive' => 'Interactif',
        'pdf' => 'PDF',
        'audio_interactive' => 'Audio + questions',
        'video_interactive' => 'Vidéo + questions',
    ];

    $difficultyLabels = [
        'easy' => 'Facile',
        'medium' => 'Moyen',
        'hard' => 'Difficile',
    ];

    $hasCourses = isset($courses) && $courses->count() > 0;
    $hasExercises = isset($exercises) && $exercises->count() > 0;
  @endphp

  <div class="za-catalogPage">
    <div class="za-catalogBg" style="background-image: url('{{ asset('images/catalog2.jpeg') }}');"></div>
    <div class="za-catalogOverlay"></div>

    <x-container class="py-10 relative z-10">
      <x-section-heading
        :title="__('ui.catalog')"
        :subtitle="__('ui.catalog_subtitle')"
      >
        <a href="{{ route('home') }}#top" class="za-backHome">
          {{ __('ui.back_home') }}
        </a>
      </x-section-heading>

      <form method="GET" action="{{ route('catalog') }}" class="za-filterCard mt-6">
        <div class="za-filterForm-grid">
          <input
            name="q"
            value="{{ request('q') }}"
            placeholder="{{ __('ui.search') }}"
            class="za-input"
            style="grid-column: 1 / -1"
          />

          <select name="track" class="za-select">
            <option value="">{{ __('ui.all_tracks') }}</option>
            @foreach($tracks as $t)
              <option value="{{ $t->slug }}" @selected((string) request('track') === (string) $t->slug)>
                {{ $t->name_i18n ?? $t->name }}
              </option>
            @endforeach
          </select>

          <select name="level" class="za-select" @disabled(blank(request('track')))>
            <option value="">{{ __('ui.all_levels') }}</option>
            @foreach($levels as $lv)
              <option value="{{ $lv->slug }}" @selected((string) request('level') === (string) $lv->slug)>
                {{ $lv->name_i18n ?? $lv->name }}
              </option>
            @endforeach
          </select>

          <select name="branch" class="za-select" @disabled(blank(request('level')))>
            <option value="">{{ __('ui.all_branches') }}</option>
            @foreach($branches as $br)
              <option value="{{ $br->slug }}" @selected((string) request('branch') === (string) $br->slug)>
                {{ $br->name_i18n ?? $br->name }}
              </option>
            @endforeach
          </select>

          <select
            name="subject"
            class="za-select"
            @disabled(blank(request('level')) || (($levelHasBranches ?? false) && blank(request('branch'))))
          >
            <option value="">{{ __('ui.all_subjects') }}</option>
            @foreach($subjects as $sb)
              <option value="{{ $sb->slug }}" @selected((string) request('subject') === (string) $sb->slug)>
                {{ $sb->name_i18n ?? $sb->name }}
              </option>
            @endforeach
          </select>

          <select name="type" class="za-select">
            <option value="">{{ __('ui.all_types') }}</option>
            <option value="course" @selected(request('type') === 'course')>{{ __('ui.type_course') }}</option>
            <option value="pack" @selected(request('type') === 'pack')>{{ __('ui.type_course_pack') }}</option>
            <option value="series" @selected(request('type') === 'series')>{{ __('ui.type_series') }}</option>
            <option value="exam" @selected(request('type') === 'exam')>{{ __('ui.type_exam') }}</option>
            <option value="exercise" @selected(request('type') === 'exercise')>{{ $tr('ui.type_exercise', 'Exercices') }}</option>
          </select>

          <select name="sort" class="za-select">
            <option value="new" @selected(request('sort', 'new') === 'new')>{{ __('ui.sort_new') }}</option>
            <option value="popular" @selected(request('sort') === 'popular')>
              {{ __('ui.sort_popular') !== 'ui.sort_popular' ? __('ui.sort_popular') : 'Les plus populaires' }}
            </option>
            <option value="featured" @selected(request('sort') === 'featured')>
              {{ __('ui.sort_featured') !== 'ui.sort_featured' ? __('ui.sort_featured') : 'Mis en avant' }}
            </option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('ui.sort_price_asc') }}</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('ui.sort_price_desc') }}</option>
          </select>
        </div>

        <div class="za-filterNote">
          <span class="za-filterNoteLabel">{{ __('ui.type_guide_label') }}</span>
          <span>{{ __('ui.type_course') }} : {{ __('ui.type_course_desc') }}</span>
          <span class="za-filterDot">•</span>
          <span>{{ __('ui.type_course_pack') }} : {{ __('ui.type_course_pack_desc') }}</span>
          <span class="za-filterDot">•</span>
          <span>{{ __('ui.type_series') }} : {{ __('ui.type_series_desc') }}</span>
          <span class="za-filterDot">•</span>
          <span>{{ __('ui.type_exam') }} : {{ __('ui.type_exam_desc') }}</span>
          <span class="za-filterDot">•</span>
          <span>{{ $tr('ui.type_exercise', 'Exercices') }} : exercices interactifs, PDF, audio ou vidéo.</span>
        </div>

        <div class="mt-4 flex items-center justify-between">
          <a href="{{ route('catalog') }}" class="za-resetLink">{{ __('ui.reset') }}</a>

          <button type="submit" class="za-applyBtn">{{ __('ui.apply') }}</button>
        </div>
      </form>

      <div class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 za-cardsWrap">
        @foreach($courses as $course)
          <div class="za-cardShell">
            <x-course-card :course="$course" />
          </div>
        @endforeach

        @foreach($exercises as $exercise)
          @php
            $user = auth()->user();

            $exerciseUrl = Route::has('exercise.show')
                ? route('exercise.show', $exercise)
                : '#';

            $exerciseTypeLabel = $typeLabels[$exercise->exercise_type] ?? 'Exercice';
            $difficultyLabel = $difficultyLabels[$exercise->difficulty] ?? null;

            $isPaid = (bool) ($exercise->is_paid ?? true);
            $exerciseQuote = ($exercisePriceQuotes[$exercise->id] ?? null)
                ?: app(\App\Services\PriceResolver::class)->resolve($exercise);
            $formattedPrice = $exerciseQuote->formattedWithHint();

            $hasExerciseAccess = ! $isPaid
                || ($user && method_exists($user, 'hasActiveEnrollmentForExercise') && $user->hasActiveEnrollmentForExercise($exercise->id));

            $exerciseCart = session('cart.exercises', []);
            $inExerciseCart = array_key_exists((string) $exercise->id, $exerciseCart);

            $canAddExerciseToCart = Route::has('cart.addExercise');
          @endphp

          <div class="za-cardShell">
            <article class="za-exerciseCard">
              <div>
                <div class="za-exerciseTop">
                  <span class="za-exerciseBadge">Exercice</span>
                  <span class="za-exerciseType">{{ $exerciseTypeLabel }}</span>
                </div>

                <h3 class="za-exerciseTitle">
                  {{ $exercise->title }}
                </h3>

                @if($exercise->description)
                  <p class="za-exerciseDesc">
                    {{ Str::limit(strip_tags($exercise->description), 120) }}
                  </p>
                @else
                  <p class="za-exerciseDesc">
                    Exercice pédagogique disponible sur Zaytouna Academy.
                  </p>
                @endif

                <div class="za-exerciseMeta">
                  @if($difficultyLabel)
                    <span>{{ $difficultyLabel }}</span>
                  @endif

                  @if($exercise->estimated_duration_minutes)
                    <span>{{ $exercise->estimated_duration_minutes }} min</span>
                  @endif

                  @if($exercise->audio_path)
                    <span>Audio</span>
                  @endif

                  @if($exercise->video_path)
                    <span>Vidéo</span>
                  @endif

                  @if($exercise->pdf_path)
                    <span>PDF</span>
                  @endif
                </div>

                <div class="za-exercisePriceRow">
                  @if($isPaid)
                    <span class="za-exercisePrice">{{ $formattedPrice }}</span>
                  @else
                    <span class="za-exerciseFree">Gratuit</span>
                  @endif
                </div>
              </div>

              <div class="za-exerciseActions">
                @if($hasExerciseAccess && $exerciseUrl !== '#')
                  <a href="{{ $exerciseUrl }}" class="za-exerciseBtn">
                    Commencer l’exercice
                  </a>
                @elseif(! auth()->check())
                  <a href="{{ route('login') }}" class="za-exerciseBtn">
                    Se connecter pour acheter
                  </a>
                @elseif($inExerciseCart)
                  <a href="{{ route('cart.show') }}" class="za-exerciseBtn">
                    Voir le panier
                  </a>
                @elseif($canAddExerciseToCart)
                  <form method="POST" action="{{ route('cart.addExercise', $exercise) }}">
                    @csrf
                    <button type="submit" class="za-exerciseBtn">
                      Ajouter au panier
                    </button>
                  </form>
                @else
                  <span class="za-exerciseBtn za-exerciseBtnDisabled">
                    Panier exercice à connecter
                  </span>
                @endif
              </div>
            </article>
          </div>
        @endforeach

        @if(! $hasCourses && ! $hasExercises)
          <div class="text-sm text-white/80 bg-black/30 border border-white/10 rounded-2xl p-4">
            Aucun contenu trouvé.
          </div>
        @endif
      </div>

      @if(request('type') === 'exercise' && method_exists($exercises, 'links'))
        <div class="mt-8 za-paginationWrap">
          {{ $exercises->links() }}
        </div>
      @elseif(method_exists($courses, 'links'))
        <div class="mt-8 za-paginationWrap">
          {{ $courses->links() }}
        </div>
      @endif
    </x-container>

    <style>
      .za-catalogPage{
        position: relative;
        min-height: calc(100vh - 80px);
        background: #0b1220;
        overflow: hidden;
      }

      .za-catalogBg{
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        transform: scale(1.03);
        filter: saturate(1.1) contrast(1.05) brightness(.75);
      }

      .za-catalogOverlay{
        position: absolute;
        inset: 0;
        background:
          radial-gradient(1200px 600px at 20% 10%, rgba(16,185,129,.22), transparent 60%),
          radial-gradient(900px 500px at 85% 35%, rgba(234,179,8,.14), transparent 55%),
          linear-gradient(to bottom, rgba(2,6,23,.82), rgba(2,6,23,.65), rgba(2,6,23,.82));
      }

      .za-backHome{
        font-size: .9rem;
        font-weight: 600;
        color: rgba(255,255,255,.82);
        padding: .35rem .65rem;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(0,0,0,.15);
        transition: .2s;
      }

      .za-backHome:hover{
        color: #fff;
        border-color: rgba(234,179,8,.35);
        background: rgba(0,0,0,.25);
      }

      .za-filterCard{
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        box-shadow: 0 22px 60px rgba(0,0,0,.35);
        padding: 1.25rem;
        backdrop-filter: blur(10px);
      }

      .za-filterForm-grid{
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: .75rem;
      }

      .za-input,
      .za-select{
        width: 100%;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,.16);
        background: rgba(2,6,23,.35);
        color: rgba(255,255,255,.92);
        font-size: .9rem;
        padding: .7rem .85rem;
        outline: none;
        transition: .18s ease;
      }

      .za-input::placeholder{
        color: rgba(255,255,255,.55);
      }

      .za-input:focus,
      .za-select:focus{
        border-color: rgba(234,179,8,.55);
        box-shadow: 0 0 0 4px rgba(234,179,8,.14);
      }

      .za-select:disabled{
        opacity: .55;
        cursor: not-allowed;
      }

      .za-filterNote{
        margin-top: .85rem;
        padding: .7rem .9rem;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.10);
        background: rgba(255,255,255,.04);
        color: rgba(255,255,255,.72);
        font-size: .8rem;
        line-height: 1.5;
      }

      .za-filterNoteLabel{
        font-weight: 700;
        color: rgba(255,255,255,.92);
        margin-inline-end: .45rem;
      }

      .za-filterDot{
        display: inline-block;
        margin: 0 .45rem;
        color: rgba(234,179,8,.75);
      }

      .za-resetLink{
        font-size: .9rem;
        color: rgba(255,255,255,.75);
        text-decoration: underline;
        text-underline-offset: 4px;
      }

      .za-resetLink:hover{
        color: #fff;
      }

      .za-applyBtn{
        border-radius: 16px;
        padding: .7rem 1.1rem;
        font-size: .9rem;
        font-weight: 700;
        color: #06121a;
        background: linear-gradient(135deg, rgba(234,179,8,1), rgba(16,185,129,.85));
        box-shadow: 0 12px 26px rgba(0,0,0,.35);
        transition: transform .15s ease, filter .2s ease;
      }

      .za-applyBtn:hover{
        transform: translateY(-1px);
        filter: brightness(1.03);
      }

      .za-cardShell{
        border-radius: 22px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.10);
        background: rgba(255,255,255,.06);
        box-shadow: 0 18px 46px rgba(0,0,0,.32);
        backdrop-filter: blur(10px);
        transition: transform .18s ease, border-color .18s ease;
      }

      .za-cardShell:hover{
        transform: translateY(-3px);
        border-color: rgba(234,179,8,.28);
      }

      .za-exerciseCard{
        height: 100%;
        min-height: 290px;
        padding: 1.1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
        background:
          radial-gradient(500px 180px at 20% 0%, rgba(234,179,8,.18), transparent 60%),
          radial-gradient(400px 180px at 90% 20%, rgba(16,185,129,.16), transparent 58%),
          rgba(2,6,23,.34);
      }

      .za-exerciseTop{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .9rem;
      }

      .za-exerciseBadge{
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .28rem .65rem;
        font-size: .72rem;
        font-weight: 800;
        color: #06121a;
        background: linear-gradient(135deg, #eab308, #10b981);
      }

      .za-exerciseType{
        color: rgba(255,255,255,.72);
        font-size: .76rem;
        font-weight: 600;
        text-align: right;
      }

      .za-exerciseTitle{
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.35;
        color: rgba(255,255,255,.96);
        margin-bottom: .55rem;
      }

      .za-exerciseDesc{
        font-size: .85rem;
        line-height: 1.55;
        color: rgba(255,255,255,.72);
        margin-bottom: .85rem;
      }

      .za-exerciseMeta{
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: .85rem;
      }

      .za-exerciseMeta span{
        border-radius: 999px;
        padding: .25rem .55rem;
        font-size: .72rem;
        color: rgba(255,255,255,.78);
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.06);
      }

      .za-exercisePriceRow{
        margin-bottom: .9rem;
      }

      .za-exercisePrice,
      .za-exerciseFree{
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .32rem .65rem;
        font-size: .78rem;
        font-weight: 900;
      }

      .za-exercisePrice{
        color: #06121a;
        background: rgba(255,255,255,.9);
      }

      .za-exerciseFree{
        color: #052e16;
        background: #86efac;
      }

      .za-exerciseActions form{
        margin: 0;
      }

      .za-exerciseBtn{
        border: 0;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        border-radius: 14px;
        padding: .72rem .9rem;
        font-size: .88rem;
        font-weight: 900;
        color: #06121a;
        background: linear-gradient(135deg, rgba(234,179,8,1), rgba(16,185,129,.9));
        box-shadow: 0 12px 26px rgba(0,0,0,.28);
        transition: transform .15s ease, filter .2s ease;
      }

      .za-exerciseBtn:hover{
        transform: translateY(-1px);
        filter: brightness(1.04);
      }

      .za-exerciseBtnDisabled{
        opacity: .65;
        cursor: not-allowed;
      }

      .za-paginationWrap{
        padding: .6rem;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.06);
        backdrop-filter: blur(10px);
        display: inline-block;
      }

      .za-paginationWrap *{
        color: rgba(255,255,255,.9) !important;
      }

      @media (max-width: 1100px){
        .za-filterForm-grid{
          grid-template-columns: repeat(3, minmax(0, 1fr));
        }
      }

      @media (max-width: 768px){
        .za-filterForm-grid{
          grid-template-columns: 1fr;
        }

        .za-filterNote{
          font-size: .76rem;
          line-height: 1.55;
        }

        .za-filterDot{
          display: none;
        }

        .za-filterNote span{
          display: block;
          margin-bottom: .25rem;
        }
      }
    </style>
  </div>
@endsection