@extends('layouts.app')

@section('content')
  @php
    $isPack            = $course->isPack();
    $isPdf             = $course->isSimpleCourse();
    $courseQuizzes     = $courseQuizzes ?? collect();
    $courseExercises   = $courseExercises ?? collect();
    $packProgress      = $packProgress ?? null;
    $packUnlockMap     = $packUnlockMap ?? [];
    $hasFullAccess     = $hasFullAccess ?? true;
    $hasPreviewAccess  = $hasPreviewAccess ?? false;
    $isPreviewMode     = !$hasFullAccess && $hasPreviewAccess;

    $title = $course->title_i18n ?? ($course->title_display ?? $course->title);
    $description = $course->description_i18n ?? ($course->description_display ?? $course->description ?? null);

    $cartCount = count(session('cart.items', []));

    $packItems = $isPack
        ? ($course->relationLoaded('packItems') ? $course->packItems : $course->packItems()->with(['linkedCourse', 'quiz', 'exercise', 'progressRecords'])->get())
        : collect();

    $orderedPackItems = $packItems->sortBy('sort_order')->values();
    $hasStructuredPackItems = $orderedPackItems->count() > 0;
  @endphp

  <div class="za-accessPage">
    <div class="za-accessBg"></div>
    <div class="za-accessOverlay"></div>

    <x-container class="py-10 relative z-10">
      <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
          <div class="za-accessEyebrow">{{ __('ui.nav.my_courses') }}</div>
          <h1 class="za-accessTitle">{{ $title }}</h1>

          @if($description)
            <p class="za-accessSubtitle">{{ $description }}</p>
          @endif

          <div class="za-accessBadges">
            <span class="za-pill">{{ $isPdf ? __('ui.type_course') : __('ui.type_course_pack') }}</span>

            @if($course->subject?->name_i18n ?? $course->subject?->name)
              <span class="za-pill za-pillSoft">
                {{ $course->subject->name_i18n ?? $course->subject->name }}
              </span>
            @endif

            @if($course->subject?->level?->name_i18n ?? $course->subject?->level?->name)
              <span class="za-pill za-pillSoft">
                {{ $course->subject->level->name_i18n ?? $course->subject->level->name }}
              </span>
            @endif

            @if($isPack && $isPreviewMode)
              <span class="za-pill za-pillPreview">Aperçu global</span>
            @endif
          </div>
        </div>

        <a href="{{ route('my.courses') }}" class="za-backBtn">
          ← {{ __('ui.back') }}
        </a>
      </div>

      <div class="mt-6 flex flex-wrap gap-2">
        <a class="za-outlineBtn" href="{{ route('catalog') }}">{{ __('ui.nav.catalog') }}</a>
        <a class="za-outlineBtn" href="{{ route('my.courses') }}">{{ __('ui.nav.my_courses') }}</a>

        @if($cartCount > 0)
          <a class="za-outlineBtn" href="{{ route('cart.show') }}">
            {{ __('ui.cart.view_cart') }} ({{ $cartCount }})
          </a>
        @endif
      </div>

      <div class="mt-8 grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8">
          @if($isPdf)
            <div class="za-card">
              <div class="za-cardHead">
                <div>
                  <div class="za-sectionKicker">{{ __('ui.type_course') }}</div>
                  <h2 class="za-cardTitle">{{ __('ui.access.your_content') }}</h2>
                </div>
                <span class="za-outlineBtn" style="font-size:.78rem;padding:.45rem .85rem;opacity:.6;cursor:default;">
                  🔒 Lecture seule
                </span>
              </div>

              <p class="za-cardText">
                {{ __('ui.access.pdf_private_note') }}
              </p>

              <div class="za-pdfViewer mt-5" id="za-pdfContainer">
                <div class="za-pdfToolbar">
                  <span id="za-pdfPageInfo" class="za-pdfToolInfo">Chargement…</span>

                  <div class="za-pdfZoomGroup">
                    <button type="button" class="za-pdfZoomBtn" onclick="zaPdfZoomOut()" title="Zoom −">−</button>
                    <span id="za-pdfZoomVal" class="za-pdfZoomVal">100%</span>
                    <button type="button" class="za-pdfZoomBtn" onclick="zaPdfZoomIn()" title="Zoom +">+</button>
                  </div>

                  <span class="za-pdfLockTag">🔒 Lecture seule</span>
                </div>

                <div class="za-pdfPages" id="za-pdfPages">
                  <div class="za-pdfLoading" id="za-pdfLoading">
                    <div class="za-pdfSpinner"></div>
                    <p>Chargement du document…</p>
                  </div>
                </div>
              </div>

              <span id="za-pdfSrc" data-url="{{ route('courses.pdf', $course) }}" style="display:none"></span>

              @if($courseQuizzes->isNotEmpty() || $courseExercises->isNotEmpty())
                <div class="za-continueSection">
                  <div class="za-continueSectionLabel">
                    ✅ PDF terminé ? Passez à l'étape suivante
                  </div>

                  <div class="za-continueSectionActions">
                    @foreach($courseQuizzes as $quiz)
                      <a href="{{ route('quiz.show', $quiz) }}" class="za-itemBtn za-itemBtnQuiz">
                        🎯 {{ $quiz->title_display ?? $quiz->title }}
                      </a>
                    @endforeach

                    @foreach($courseExercises as $exercise)
                      <a href="{{ route('exercise.show', $exercise) }}" class="za-itemBtn za-itemBtnExercise">
                        ✏️ {{ $exercise->title_display ?? $exercise->title }}
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>
          @endif

          @if($isPack)
            <div class="za-card">
              <div class="za-cardHead">
                <div>
                  <div class="za-sectionKicker">{{ __('ui.type_course_pack') }}</div>
                  <h2 class="za-cardTitle">Parcours du pack</h2>
                </div>

                @if($hasStructuredPackItems)
                  <span class="za-counter">{{ $orderedPackItems->count() }} élément{{ $orderedPackItems->count() > 1 ? 's' : '' }}</span>
                @endif
              </div>

              @if($hasStructuredPackItems)
                <p class="za-cardText">
                  Retrouvez ci-dessous le contenu complet de votre pack : cours liés, séries, quiz, ressources et préparation à l’examen.
                </p>

                @if($isPreviewMode)
                  <div class="za-noteBox mt-6 za-previewBox">
                    <strong>Mode aperçu du pack</strong><br>
                    Vous consultez le pack en aperçu global. Les cours internes, quiz et exercices seront accessibles après achat.
                  </div>
                @endif

                <div class="za-timeline mt-8">
                  @foreach($orderedPackItems as $index => $item)
                    @php
                      $itemTypeLabel = match($item->item_type) {
                          'course'    => 'Course liée',
                          'series'    => 'Série',
                          'quiz'      => 'Quiz',
                          'exercise'  => 'Exercice',
                          'exam_prep' => 'Préparation examen',
                          'resource'  => 'Ressource',
                          default     => 'Contenu',
                      };

                      $linkedCourseUrl = null;
                      if ($item->linkedCourse && \Illuminate\Support\Facades\Route::has('courses.access')) {
                          $linkedCourseUrl = route('courses.access', $item->linkedCourse);
                      }

                      $userProgress = $item->progressRecords->first();
                      $isCompleted = $userProgress?->is_completed ?? false;
                      $isStarted = $userProgress?->is_started ?? false;

                      $quiz = $item->quiz ?? null;
                      $quizUrl = null;
                      if ($quiz && $quiz->is_published && \Illuminate\Support\Facades\Route::has('quiz.show')) {
                          $quizUrl = route('quiz.show', $quiz);
                      }

                      $exercise = $item->exercise ?? null;
                      $exerciseUrl = null;
                      if ($exercise && $exercise->is_published && \Illuminate\Support\Facades\Route::has('exercise.show')) {
                          $exerciseUrl = route('exercise.show', $exercise);
                      }

                      $itemDescription = $item->description_i18n ?? $item->description_display ?? $item->description;

                      $unlockData = $packUnlockMap[$item->id] ?? ['is_unlocked' => true, 'message' => null];
                      $isUnlocked = !$isPreviewMode && (bool) ($unlockData['is_unlocked'] ?? true);
                      $lockMessage = $isPreviewMode
                          ? 'Disponible après achat du pack.'
                          : ($unlockData['message'] ?? null);

                      $statusLabel = 'Disponible';
                      $statusClass = 'available';

                      if (!$isUnlocked) {
                          $statusLabel = 'Verrouillé';
                          $statusClass = 'locked';
                      } elseif ($isCompleted) {
                          $statusLabel = 'Terminé';
                          $statusClass = 'done';
                      } elseif ($isStarted) {
                          $statusLabel = 'En cours';
                          $statusClass = 'started';
                      }

                      $isLast = $index === $orderedPackItems->count() - 1;
                    @endphp

                    <div class="za-timelineItem {{ $isLast ? 'is-last' : '' }}">
                      <div class="za-timelineRail">
                        <div class="za-timelineDot is-{{ $statusClass }}"></div>
                        @if(!$isLast)
                          <div class="za-timelineLine is-{{ $statusClass }}"></div>
                        @endif
                      </div>

                      <div class="za-packItem">
                        @php
                          $typeClass = match($item->item_type) {
                              'quiz'      => 'za-packType za-packTypeQuiz',
                              'exercise'  => 'za-packType za-packTypeExercise',
                              'series'    => 'za-packType za-packTypeSeries',
                              'exam_prep' => 'za-packType za-packTypeExam',
                              default     => 'za-packType',
                          };
                        @endphp

                        <div class="za-packItemTop">
                          <div class="za-packItemTopLeft">
                            <span class="za-packOrder">#{{ $item->sort_order }}</span>
                            <span class="{{ $typeClass }}">{{ $itemTypeLabel }}</span>

                            @if($item->is_required)
                              <span class="za-packMiniTag">Obligatoire</span>
                            @endif

                            @if($isCompleted)
                              <span class="za-packMiniTag za-packMiniTagDone">Terminé</span>
                            @elseif($isStarted)
                              <span class="za-packMiniTag za-packMiniTagStarted">En cours</span>
                            @endif

                            @if(!$isUnlocked)
                              <span class="za-packMiniTag za-packMiniTagLocked">Verrouillé</span>
                            @endif
                          </div>

                          <div class="za-packItemTopRight">
                            <span class="za-statusBadge is-{{ $statusClass }}">{{ $statusLabel }}</span>

                            @if($item->duration_minutes)
                              <span class="za-packDuration">{{ $item->duration_minutes }} min</span>
                            @endif
                          </div>
                        </div>

                        <h3 class="za-packItemTitle">
                          {{ $item->title_i18n ?? $item->title_display ?? $item->title }}
                        </h3>

                        @if($itemDescription)
                          <p class="za-packItemText">{{ $itemDescription }}</p>
                        @endif

                        <div class="za-packActions">
                          @if($isPreviewMode)
                            <span class="za-inlineInfo">
                              Contenu visible après achat du pack.
                            </span>
                          @else
                            @if($item->item_type === 'quiz')
                              @if(!$isUnlocked)
                                <span class="za-inlineInfo">
                                  {{ $lockMessage }}
                                </span>
                              @elseif($quizUrl)
                                <form method="POST" action="{{ route('pack.items.start', [$course, $item]) }}" class="za-inlineForm">
                                  @csrf
                                  <button type="submit" class="za-itemBtn za-itemBtnSoft">
                                    Marquer commencé
                                  </button>
                                </form>

                                <a href="{{ $quizUrl }}" class="za-itemBtn za-itemBtnQuiz">
                                  {{ $isCompleted ? 'Revoir le quiz' : 'Commencer le quiz' }}
                                </a>

                                @if(!$isCompleted)
                                  <form method="POST" action="{{ route('pack.items.complete', [$course, $item]) }}" class="za-inlineForm">
                                    @csrf
                                    <button type="submit" class="za-itemBtn">
                                      Marquer terminé
                                    </button>
                                  </form>
                                @endif
                              @elseif($quiz)
                                <span class="za-inlineInfo">
                                  Quiz non publié
                                </span>
                              @else
                                <span class="za-inlineInfo">
                                  Quiz non encore lié
                                </span>
                              @endif
                            @endif

                            @if($item->item_type === 'exercise')
                              @if(!$isUnlocked)
                                <span class="za-inlineInfo">{{ $lockMessage }}</span>
                              @elseif($exerciseUrl)
                                <form method="POST" action="{{ route('pack.items.start', [$course, $item]) }}" class="za-inlineForm">
                                  @csrf
                                  <button type="submit" class="za-itemBtn za-itemBtnSoft">
                                    Marquer commencé
                                  </button>
                                </form>

                                <a href="{{ $exerciseUrl }}" class="za-itemBtn za-itemBtnExercise">
                                  {{ $isCompleted ? 'Revoir l\'exercice' : 'Commencer l\'exercice' }}
                                </a>

                                @if(!$isCompleted)
                                  <form method="POST" action="{{ route('pack.items.complete', [$course, $item]) }}" class="za-inlineForm">
                                    @csrf
                                    <button type="submit" class="za-itemBtn">Marquer terminé</button>
                                  </form>
                                @endif
                              @elseif($exercise)
                                <span class="za-inlineInfo">Exercice non publié</span>
                              @else
                                <span class="za-inlineInfo">Exercice non encore lié</span>
                              @endif
                            @endif

                            @if(in_array($item->item_type, ['course', 'series', 'resource', 'exam_prep']))
                              @if(!$isUnlocked)
                                <span class="za-inlineInfo">
                                  {{ $lockMessage }}
                                </span>
                              @else
                                @if($item->item_type === 'course' && $linkedCourseUrl)
                                  <a href="{{ $linkedCourseUrl }}" class="za-itemBtn">
                                    Ouvrir la course
                                  </a>
                                @endif

                                @if(!$isStarted)
                                  <form method="POST" action="{{ route('pack.items.start', [$course, $item]) }}" class="za-inlineForm">
                                    @csrf
                                    <button type="submit" class="za-itemBtn za-itemBtnSoft">
                                      Commencer
                                    </button>
                                  </form>
                                @endif

                                @if(!$isCompleted)
                                  <form method="POST" action="{{ route('pack.items.complete', [$course, $item]) }}" class="za-inlineForm">
                                    @csrf
                                    <button type="submit" class="za-itemBtn">
                                      Marquer terminé
                                    </button>
                                  </form>
                                @endif
                              @endif
                            @endif

                            @if($item->resource_path)
                              <span class="za-inlineInfo">
                                Ressource privée disponible
                              </span>
                            @endif

                            @if($item->external_url && $isUnlocked)
                              <a href="{{ $item->external_url }}" target="_blank" rel="noopener" class="za-itemBtn za-itemBtnSoft">
                                Ouvrir le contenu
                              </a>
                            @endif

                            @if(
                              $item->item_type === 'course' &&
                              !$linkedCourseUrl &&
                              $item->linkedCourse &&
                              $isUnlocked
                            )
                              <span class="za-inlineInfo">
                                Route d’accès du cours liée à brancher
                              </span>
                            @endif
                          @endif
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <p class="za-cardText">
                  Ce pack utilise encore le mode d’accès externe. Vous serez redirigé vers la plateforme ou les ressources associées.
                </p>

                @if($course->access_note)
                  <div class="za-noteBox mt-6">
                    {!! nl2br(e($course->access_note)) !!}
                  </div>
                @endif

                @if($course->external_url)
                  <div class="mt-6">
                    <a href="{{ $course->external_url }}" target="_blank" rel="noopener" class="za-primaryBtn">
                      {{ __('ui.access.go_live') }} →
                    </a>
                  </div>
                @endif
              @endif
            </div>
          @endif
        </div>

        <div class="xl:col-span-4">
          <div class="za-card za-stickyCard">
            <h3 class="za-sideTitle">Vue d’ensemble</h3>

            @if($isPack && $packProgress && !$isPreviewMode)
              <div class="za-globalProgress">
                <div class="za-globalProgressTop">
                  <span>Progression globale</span>
                  <strong>{{ $packProgress['percentage'] }}%</strong>
                </div>

                <div class="za-globalBar">
                  <div class="za-globalBarFill" style="width: {{ $packProgress['percentage'] }}%"></div>
                </div>

                <div class="za-globalMeta">
                  {{ $packProgress['completed_items'] }} / {{ $packProgress['total_items'] }} éléments obligatoires terminés
                </div>
              </div>
            @endif

            @if($isPack && $isPreviewMode)
              <div class="za-sideBox za-sideBoxPreview">
                <h4>Aperçu du pack</h4>
                <p>
                  Vous voyez ici la structure générale du pack. L’ouverture des cours, quiz et exercices sera disponible après achat.
                </p>
              </div>
            @endif

            <div class="za-sideStats">
              <div class="za-statRow">
                <span>Type</span>
                <strong>{{ $isPdf ? __('ui.type_course') : __('ui.type_course_pack') }}</strong>
              </div>

              @if($isPack)
                <div class="za-statRow">
                  <span>Éléments</span>
                  <strong>{{ $orderedPackItems->count() }}</strong>
                </div>

                <div class="za-statRow">
                  <span>Quiz</span>
                  <strong>{{ $orderedPackItems->where('item_type', 'quiz')->count() }}</strong>
                </div>

                @if($orderedPackItems->where('item_type', 'exercise')->count() > 0)
                  <div class="za-statRow">
                    <span>Exercices</span>
                    <strong>{{ $orderedPackItems->where('item_type', 'exercise')->count() }}</strong>
                  </div>
                @endif

                <div class="za-statRow">
                  <span>Courses liées</span>
                  <strong>{{ $orderedPackItems->where('item_type', 'course')->count() }}</strong>
                </div>
              @endif

              @if($course->subject?->name_i18n ?? $course->subject?->name)
                <div class="za-statRow">
                  <span>Matière</span>
                  <strong>{{ $course->subject->name_i18n ?? $course->subject->name }}</strong>
                </div>
              @endif
            </div>

            @if($isPack && $hasStructuredPackItems)
              <div class="za-sideBox">
                <h4>Parcours complet</h4>
                <p>
                  Ce pack peut contenir plusieurs courses, séries, quiz et étapes de préparation à l’examen.
                </p>
              </div>
            @endif

            @if($isPdf)
              <div class="za-sideBox">
                <h4>Accès sécurisé</h4>
                <p>Votre course est disponible de manière privée et sécurisée depuis votre espace.</p>
              </div>

              @if($courseQuizzes->isNotEmpty())
                <div class="za-sideBox" style="border-color:rgba(59,130,246,.22);background:rgba(59,130,246,.07);">
                  <h4 style="color:#bfdbfe;">Quiz associés</h4>
                  <div class="mt-2 space-y-2">
                    @foreach($courseQuizzes as $quiz)
                      <a href="{{ route('quiz.show', $quiz) }}" class="za-itemBtn za-itemBtnQuiz" style="display:flex;font-size:.82rem;padding:.6rem .85rem;">
                        🎯 {{ $quiz->title_display ?? $quiz->title }}
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif

              @if($courseExercises->isNotEmpty())
                <div class="za-sideBox" style="border-color:rgba(139,92,246,.22);background:rgba(139,92,246,.07);">
                  <h4 style="color:#e9d5ff;">Exercices associés</h4>
                  <div class="mt-2 space-y-2">
                    @foreach($courseExercises as $exercise)
                      <a href="{{ route('exercise.show', $exercise) }}" class="za-itemBtn za-itemBtnExercise" style="display:flex;font-size:.82rem;padding:.6rem .85rem;text-decoration:none;">
                        ✏️ {{ $exercise->title_display ?? $exercise->title }}
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            @endif
          </div>
        </div>
      </div>
    </x-container>

    <style>
      .za-accessPage{
        position:relative;
        min-height:calc(100vh - 80px);
        overflow:hidden;
        background:#08111d;
      }

      .za-accessBg{
        position:absolute; inset:0;
        background:
          radial-gradient(1200px 600px at 15% 10%, rgba(16,185,129,.14), transparent 60%),
          radial-gradient(900px 500px at 85% 20%, rgba(234,179,8,.10), transparent 55%),
          linear-gradient(180deg, #08111d 0%, #0b1726 100%);
      }

      .za-accessOverlay{
        position:absolute; inset:0;
        background:linear-gradient(to bottom, rgba(2,6,23,.35), rgba(2,6,23,.15), rgba(2,6,23,.35));
      }

      .za-accessEyebrow{
        font-size:.9rem;
        color:rgba(255,255,255,.72);
      }

      .za-accessTitle{
        margin-top:.35rem;
        font-size:clamp(2rem, 4vw, 3rem);
        line-height:1.05;
        font-weight:900;
        color:#fff;
      }

      .za-accessSubtitle{
        margin-top:.9rem;
        max-width:850px;
        color:rgba(255,255,255,.76);
        line-height:1.7;
        font-size:.98rem;
      }

      .za-accessBadges{
        display:flex;
        flex-wrap:wrap;
        gap:.55rem;
        margin-top:1rem;
      }

      .za-pill{
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:.45rem .8rem;
        font-size:.82rem;
        font-weight:700;
        color:#ecfdf5;
        background:rgba(16,185,129,.14);
        border:1px solid rgba(16,185,129,.30);
      }

      .za-pillSoft{
        color:rgba(255,255,255,.9);
        background:rgba(255,255,255,.06);
        border:1px solid rgba(255,255,255,.10);
      }

      .za-pillPreview{
        color:#dbeafe;
        background:rgba(59,130,246,.14);
        border:1px solid rgba(59,130,246,.30);
      }

      .za-backBtn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:999px;
        padding:.7rem 1rem;
        font-size:.92rem;
        font-weight:700;
        color:#fff;
        border:1px solid rgba(255,255,255,.12);
        background:rgba(255,255,255,.06);
        transition:.18s ease;
      }

      .za-backBtn:hover{
        transform:translateY(-1px);
        background:rgba(255,255,255,.09);
      }

      .za-outlineBtn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:14px;
        padding:.7rem 1rem;
        font-size:.9rem;
        font-weight:600;
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.05);
        transition:.18s ease;
      }

      .za-outlineBtn:hover{
        transform:translateY(-1px);
        background:rgba(255,255,255,.08);
      }

      .za-card{
        border-radius:28px;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.06);
        box-shadow:0 22px 60px rgba(0,0,0,.30);
        backdrop-filter:blur(12px);
        padding:1.4rem;
      }

      .za-cardHead{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        flex-wrap:wrap;
      }

      .za-sectionKicker{
        font-size:.8rem;
        font-weight:800;
        letter-spacing:.05em;
        text-transform:uppercase;
        color:rgba(234,179,8,.88);
        margin-bottom:.4rem;
      }

      .za-cardTitle{
        font-size:1.45rem;
        line-height:1.2;
        font-weight:800;
        color:#fff;
      }

      .za-counter{
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:.45rem .8rem;
        font-size:.8rem;
        font-weight:700;
        color:rgba(255,255,255,.92);
        background:rgba(255,255,255,.06);
        border:1px solid rgba(255,255,255,.10);
      }

      .za-cardText{
        margin-top:.9rem;
        color:rgba(255,255,255,.76);
        line-height:1.7;
        font-size:.96rem;
      }

      .za-primaryBtn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:16px;
        padding:.9rem 1.2rem;
        font-size:.95rem;
        font-weight:800;
        color:#07111d;
        background:linear-gradient(135deg, rgba(234,179,8,1), rgba(16,185,129,.92));
        box-shadow:0 14px 32px rgba(0,0,0,.25);
        transition:.18s ease;
      }

      .za-primaryBtn:hover{
        transform:translateY(-1px);
        filter:brightness(1.03);
      }

      .za-timeline{
        position:relative;
        display:grid;
        gap:1rem;
      }

      .za-timelineItem{
        display:grid;
        grid-template-columns: 34px 1fr;
        gap:1rem;
        align-items:stretch;
      }

      .za-timelineRail{
        position:relative;
        display:flex;
        flex-direction:column;
        align-items:center;
      }

      .za-timelineDot{
        width:18px;
        height:18px;
        border-radius:999px;
        border:3px solid rgba(255,255,255,.18);
        background:rgba(255,255,255,.08);
        position:relative;
        z-index:2;
        box-shadow:0 0 0 8px rgba(255,255,255,.02);
      }

      .za-timelineDot.is-done{
        background:#10b981;
        border-color:#34d399;
        box-shadow:0 0 0 8px rgba(16,185,129,.10);
      }

      .za-timelineDot.is-started{
        background:#3b82f6;
        border-color:#60a5fa;
        box-shadow:0 0 0 8px rgba(59,130,246,.10);
      }

      .za-timelineDot.is-available{
        background:#f59e0b;
        border-color:#fbbf24;
        box-shadow:0 0 0 8px rgba(245,158,11,.10);
      }

      .za-timelineDot.is-locked{
        background:#f43f5e;
        border-color:#fb7185;
        box-shadow:0 0 0 8px rgba(244,63,94,.10);
      }

      .za-timelineLine{
        width:2px;
        flex:1;
        margin-top:.4rem;
        min-height:36px;
        border-radius:999px;
        background:rgba(255,255,255,.10);
      }

      .za-timelineLine.is-done{
        background:linear-gradient(to bottom, rgba(16,185,129,.65), rgba(16,185,129,.16));
      }

      .za-timelineLine.is-started{
        background:linear-gradient(to bottom, rgba(59,130,246,.65), rgba(59,130,246,.16));
      }

      .za-timelineLine.is-available{
        background:linear-gradient(to bottom, rgba(245,158,11,.65), rgba(245,158,11,.16));
      }

      .za-timelineLine.is-locked{
        background:linear-gradient(to bottom, rgba(244,63,94,.55), rgba(244,63,94,.14));
      }

      .za-packItem{
        border-radius:22px;
        border:1px solid rgba(255,255,255,.08);
        background:rgba(2,6,23,.24);
        padding:1rem;
        transition:.18s ease;
      }

      .za-packItem:hover{
        border-color:rgba(234,179,8,.18);
        transform:translateY(-1px);
      }

      .za-packItemTop{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        flex-wrap:wrap;
      }

      .za-packItemTopLeft,
      .za-packItemTopRight{
        display:flex;
        align-items:center;
        flex-wrap:wrap;
        gap:.45rem;
      }

      .za-packOrder,
      .za-packType,
      .za-packMiniTag,
      .za-packDuration,
      .za-statusBadge{
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:.35rem .65rem;
        font-size:.76rem;
        font-weight:700;
      }

      .za-packOrder{
        color:#fff;
        background:rgba(255,255,255,.06);
        border:1px solid rgba(255,255,255,.10);
      }

      .za-packType{
        color:#d1fae5;
        background:rgba(16,185,129,.14);
        border:1px solid rgba(16,185,129,.28);
      }

      .za-packTypeQuiz{
        color:#bfdbfe;
        background:rgba(59,130,246,.14);
        border:1px solid rgba(59,130,246,.28);
      }

      .za-packTypeExercise{
        color:#e9d5ff;
        background:rgba(139,92,246,.14);
        border:1px solid rgba(139,92,246,.28);
      }

      .za-packTypeSeries{
        color:#fed7aa;
        background:rgba(249,115,22,.14);
        border:1px solid rgba(249,115,22,.28);
      }

      .za-packTypeExam{
        color:#fecdd3;
        background:rgba(244,63,94,.14);
        border:1px solid rgba(244,63,94,.28);
      }

      .za-packMiniTag{
        color:#fef3c7;
        background:rgba(234,179,8,.12);
        border:1px solid rgba(234,179,8,.26);
      }

      .za-packMiniTagDone{
        color:#d1fae5;
        background:rgba(16,185,129,.14);
        border:1px solid rgba(16,185,129,.30);
      }

      .za-packMiniTagStarted{
        color:#dbeafe;
        background:rgba(59,130,246,.14);
        border:1px solid rgba(59,130,246,.30);
      }

      .za-packMiniTagLocked{
        color:#fecdd3;
        background:rgba(244,63,94,.14);
        border:1px solid rgba(244,63,94,.30);
      }

      .za-statusBadge{
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.06);
      }

      .za-statusBadge.is-done{
        color:#d1fae5;
        background:rgba(16,185,129,.14);
        border-color:rgba(16,185,129,.32);
      }

      .za-statusBadge.is-started{
        color:#dbeafe;
        background:rgba(59,130,246,.14);
        border-color:rgba(59,130,246,.32);
      }

      .za-statusBadge.is-available{
        color:#fef3c7;
        background:rgba(245,158,11,.14);
        border-color:rgba(245,158,11,.30);
      }

      .za-statusBadge.is-locked{
        color:#fecdd3;
        background:rgba(244,63,94,.14);
        border-color:rgba(244,63,94,.30);
      }

      .za-packDuration{
        color:rgba(255,255,255,.88);
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.08);
      }

      .za-packItemTitle{
        margin-top:.9rem;
        color:#fff;
        font-size:1.08rem;
        font-weight:800;
        line-height:1.35;
      }

      .za-packItemText{
        margin-top:.55rem;
        color:rgba(255,255,255,.76);
        line-height:1.65;
        font-size:.93rem;
      }

      .za-packActions{
        display:flex;
        flex-wrap:wrap;
        gap:.65rem;
        margin-top:1rem;
      }

      .za-inlineForm{
        display:inline-flex;
      }

      .za-itemBtn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:14px;
        padding:.75rem 1rem;
        font-size:.88rem;
        font-weight:700;
        color:#07111d;
        background:#fff;
        transition:.18s ease;
        border:none;
        cursor:pointer;
      }

      .za-itemBtn:hover{
        transform:translateY(-1px);
      }

      .za-itemBtnQuiz{
        background:linear-gradient(135deg, rgba(16,185,129,1), rgba(59,130,246,.95));
        color:#fff;
      }

      .za-itemBtnSoft{
        background:rgba(255,255,255,.08);
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
      }

      .za-itemBtnExercise{
        background:linear-gradient(135deg, rgba(139,92,246,1), rgba(59,130,246,.95));
        color:#fff;
      }

      .za-pdfViewer{
        border-radius:18px;
        overflow:hidden;
        border:1px solid rgba(255,255,255,.1);
        background:#111827;
        user-select:none;
        -webkit-user-select:none;
      }

      .za-pdfToolbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:.65rem 1rem;
        background:rgba(255,255,255,.06);
        border-bottom:1px solid rgba(255,255,255,.08);
        flex-wrap:wrap;
      }

      .za-pdfToolInfo{
        font-size:.78rem;
        color:rgba(255,255,255,.55);
        font-weight:600;
      }

      .za-pdfZoomGroup{
        display:flex;
        align-items:center;
        gap:.5rem;
      }

      .za-pdfZoomBtn{
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.12);
        color:#fff;
        border-radius:8px;
        width:28px;
        height:28px;
        font-size:1rem;
        line-height:1;
        cursor:pointer;
        transition:.15s;
      }

      .za-pdfZoomBtn:hover{
        background:rgba(255,255,255,.14);
      }

      .za-pdfZoomVal{
        font-size:.8rem;
        font-weight:700;
        color:rgba(255,255,255,.7);
        min-width:38px;
        text-align:center;
      }

      .za-pdfLockTag{
        font-size:.72rem;
        font-weight:700;
        color:rgba(255,255,255,.35);
        letter-spacing:.03em;
      }

      .za-pdfPages{
        max-height:76vh;
        overflow-y:auto;
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:16px;
        padding:1rem;
        background:#0f172a;
        scrollbar-width:thin;
        scrollbar-color:rgba(255,255,255,.15) transparent;
      }

      .za-pdfPageWrap{
        position:relative;
        width:100%;
        display:flex;
        justify-content:center;
      }

      .za-pdfPageWrap canvas{
        display:block;
        width:auto !important;
        max-width:100%;
        height:auto !important;
        border-radius:8px;
        background:#fff;
        box-shadow:0 6px 24px rgba(0,0,0,.45);
      }

      .za-pdfPageNum{
        position:absolute;
        bottom:8px;
        right:12px;
        font-size:.68rem;
        font-weight:700;
        color:rgba(255,255,255,.65);
        background:rgba(0,0,0,.45);
        border-radius:6px;
        padding:.2rem .45rem;
        pointer-events:none;
      }

      .za-pdfLoading{
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:1rem;
        padding:3rem 1rem;
        color:rgba(255,255,255,.45);
        font-size:.9rem;
      }

      @keyframes zaSpin{
        to{ transform:rotate(360deg); }
      }

      .za-pdfSpinner{
        width:36px;
        height:36px;
        border:3px solid rgba(255,255,255,.1);
        border-top-color:#10b981;
        border-radius:50%;
        animation:zaSpin .8s linear infinite;
      }

      @media(max-width:768px){
        .za-pdfPages{
          max-height:58vh;
        }
      }

      .za-continueSection{
        margin-top:1.4rem;
        border-radius:20px;
        border:1px solid rgba(16,185,129,.25);
        background:rgba(16,185,129,.07);
        padding:1.1rem 1.2rem;
      }

      .za-continueSectionLabel{
        color:#ecfdf5;
        font-size:.92rem;
        font-weight:800;
        margin-bottom:.9rem;
      }

      .za-continueSectionActions{
        display:flex;
        flex-wrap:wrap;
        gap:.75rem;
      }

      .za-inlineInfo{
        display:inline-flex;
        align-items:center;
        border-radius:14px;
        padding:.72rem .95rem;
        font-size:.84rem;
        font-weight:600;
        color:rgba(255,255,255,.76);
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.08);
      }

      .za-noteBox{
        border-radius:20px;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.05);
        padding:1rem;
        color:rgba(255,255,255,.88);
        line-height:1.7;
      }

      .za-previewBox{
        border-color:rgba(59,130,246,.25);
        background:rgba(59,130,246,.10);
      }

      .za-sideBoxPreview{
        background:rgba(59,130,246,.10);
        border-color:rgba(59,130,246,.22);
      }

      .za-stickyCard{
        position:sticky;
        top:100px;
      }

      .za-sideTitle{
        color:#fff;
        font-size:1.1rem;
        font-weight:800;
        margin-bottom:1rem;
      }

      .za-globalProgress{
        margin-bottom:1rem;
        padding:1rem;
        border-radius:18px;
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.08);
      }

      .za-globalProgressTop{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:.7rem;
      }

      .za-globalProgressTop span{
        color:rgba(255,255,255,.72);
        font-size:.9rem;
      }

      .za-globalProgressTop strong{
        color:#fff;
        font-size:1rem;
        font-weight:900;
      }

      .za-globalBar{
        height:10px;
        border-radius:999px;
        overflow:hidden;
        background:rgba(255,255,255,.08);
      }

      .za-globalBarFill{
        height:100%;
        border-radius:999px;
        background:linear-gradient(90deg, rgba(16,185,129,1), rgba(234,179,8,1));
        transition:width .35s ease;
      }

      .za-globalMeta{
        margin-top:.65rem;
        color:rgba(255,255,255,.72);
        font-size:.82rem;
      }

      .za-sideStats{
        display:grid;
        gap:.8rem;
      }

      .za-statRow{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:.8rem .9rem;
        border-radius:16px;
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.08);
      }

      .za-statRow span{
        color:rgba(255,255,255,.72);
        font-size:.88rem;
      }

      .za-statRow strong{
        color:#fff;
        font-size:.92rem;
        font-weight:800;
        text-align:right;
      }

      .za-sideBox{
        margin-top:1rem;
        border-radius:18px;
        padding:1rem;
        background:rgba(16,185,129,.08);
        border:1px solid rgba(16,185,129,.18);
      }

      .za-sideBox h4{
        color:#ecfdf5;
        font-size:.95rem;
        font-weight:800;
        margin-bottom:.4rem;
      }

      .za-sideBox p{
        color:rgba(255,255,255,.78);
        line-height:1.65;
        font-size:.9rem;
      }

      @media (max-width: 1279px){
        .za-stickyCard{
          position:static;
        }
      }

      @media (max-width: 768px){
        .za-card{
          padding:1rem;
          border-radius:22px;
        }

        .za-accessTitle{
          font-size:2rem;
        }

        .za-packActions,
        .za-accessBadges{
          gap:.5rem;
        }

        .za-timelineItem{
          grid-template-columns: 24px 1fr;
          gap:.8rem;
        }

        .za-itemBtn,
        .za-outlineBtn,
        .za-primaryBtn,
        .za-backBtn{
          width:100%;
          text-align:center;
          justify-content:center;
        }

        .za-inlineForm{
          width:100%;
        }
      }
    </style>

    @if($isPdf)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>
    <script>
    (function () {
      'use strict';

      pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

      let pdfDoc = null;
      let zoom = 1;
      let pdfUrl = document.getElementById('za-pdfSrc')?.dataset.url || '';
      let pagesEl = document.getElementById('za-pdfPages');
      let infoEl = document.getElementById('za-pdfPageInfo');
      let zoomEl = document.getElementById('za-pdfZoomVal');
      let renderToken = 0;

      async function initPdf() {
        if (!pdfUrl || !pagesEl) return;

        try {
          const resp = await fetch(pdfUrl, { credentials: 'same-origin' });
          if (!resp.ok) throw new Error('HTTP ' + resp.status);

          const buffer = await resp.arrayBuffer();
          pdfDoc = await pdfjsLib.getDocument({ data: buffer }).promise;

          const loadEl = document.getElementById('za-pdfLoading');
          if (loadEl) loadEl.remove();

          if (infoEl) {
            infoEl.textContent = `${pdfDoc.numPages} page${pdfDoc.numPages > 1 ? 's' : ''}`;
          }

          await renderAllPages();
        } catch (err) {
          const loadEl = document.getElementById('za-pdfLoading');
          if (loadEl) {
            loadEl.innerHTML = '<p style="color:#ef4444;padding:1rem;">Impossible de charger le document.</p>';
          }
          console.error(err);
        }
      }

      function getAvailableWidth() {
        if (!pagesEl) return 700;
        const width = pagesEl.clientWidth - 32;
        return Math.max(width, 260);
      }

      async function renderAllPages() {
        if (!pdfDoc || !pagesEl) return;

        const token = ++renderToken;
        pagesEl.innerHTML = '';
        if (zoomEl) zoomEl.textContent = `${Math.round(zoom * 100)}%`;

        const availableWidth = getAvailableWidth();

        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
          if (token !== renderToken) return;

          const page = await pdfDoc.getPage(pageNum);

          const baseViewport = page.getViewport({ scale: 1 });
          const fitScale = availableWidth / baseViewport.width;
          const scale = fitScale * zoom;

          const viewport = page.getViewport({ scale });
          const dpr = window.devicePixelRatio || 1;

          const canvas = document.createElement('canvas');
          const context = canvas.getContext('2d');

          canvas.width = Math.floor(viewport.width * dpr);
          canvas.height = Math.floor(viewport.height * dpr);

          canvas.style.width = `${Math.floor(viewport.width)}px`;
          canvas.style.height = `${Math.floor(viewport.height)}px`;

          context.setTransform(dpr, 0, 0, dpr, 0, 0);

          const wrap = document.createElement('div');
          wrap.className = 'za-pdfPageWrap';

          const badge = document.createElement('div');
          badge.className = 'za-pdfPageNum';
          badge.textContent = `${pageNum} / ${pdfDoc.numPages}`;

          wrap.appendChild(canvas);
          wrap.appendChild(badge);
          pagesEl.appendChild(wrap);

          await page.render({
            canvasContext: context,
            viewport: viewport
          }).promise;
        }
      }

      let resizeTimer = null;
      window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
          renderAllPages();
        }, 180);
      });

      window.zaPdfZoomIn = function () {
        if (zoom < 2.5) {
          zoom = Math.round((zoom + 0.2) * 100) / 100;
          renderAllPages();
        }
      };

      window.zaPdfZoomOut = function () {
        if (zoom > 0.6) {
          zoom = Math.round((zoom - 0.2) * 100) / 100;
          renderAllPages();
        }
      };

      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && ['s','p','u','a'].includes(e.key.toLowerCase())) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);

      const container = document.getElementById('za-pdfContainer');
      if (container) {
        container.addEventListener('contextmenu', function (e) { e.preventDefault(); });
        container.addEventListener('dragstart', function (e) { e.preventDefault(); });
      }

      initPdf();
    })();
    </script>
    @endif
  </div>
@endsection