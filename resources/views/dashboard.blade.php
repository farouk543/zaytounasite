@extends('layouts.app')

@section('content')
  @php
    $user = auth()->user();

    $dashboardStats = $dashboardStats ?? [
        'active_products' => 0,
        'completed_quizzes' => 0,
        'average_score' => 0,
        'average_progress' => 0,
    ];

    $learningProducts = $learningProducts ?? collect();
    $recentQuizAttempts = $recentQuizAttempts ?? collect();
    $pendingOrders = $pendingOrders ?? collect();
    $continueUrl = $continueUrl ?? route('my.courses');
    $continueLabel = $continueLabel ?? __('ui.nav.my_courses');
    $continueMeta = $continueMeta ?? null;
    $continueType = $continueType ?? null;
    $continueProgress = $continueProgress ?? 0;
  @endphp

  <div class="za-dashboardPage">
    <div class="za-dashboardBg"></div>
    <div class="za-dashboardOverlay"></div>

    <x-container class="py-10 relative z-10">
      <x-section-heading
        :title="__('ui.nav.dashboard')"
        :subtitle="__('ui.dashboard.logged_in')"
      />

      {{-- ADMIN --}}
      @if($user && $user->hasRole('admin'))
        <div class="za-roleCard mt-6">
          <div class="text-lg font-extrabold text-white">{{ __('ui.dashboard.admin_title') }}</div>
          <p class="mt-2 text-sm text-white/70">
            {{ __('ui.dashboard.admin_subtitle') }}
          </p>

          <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ url('/admin') }}" class="za-btnPrimary">{{ __('ui.dashboard.go_backoffice') }}</a>
            <a href="{{ route('catalog') }}" class="za-btnOutline">{{ __('ui.dashboard.view_catalog') }}</a>
            <a href="{{ route('my.courses') }}" class="za-btnOutline">{{ __('ui.nav.my_courses') }}</a>
          </div>
        </div>

      {{-- MANAGER --}}
      @elseif($user && $user->hasRole('manager'))
        <div class="za-roleCard mt-6">
          <div class="text-lg font-extrabold text-white">{{ __('ui.dashboard.manager_title') }}</div>
          <p class="mt-2 text-sm text-white/70">
            {{ __('ui.dashboard.manager_subtitle') }}
          </p>

          <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ url('/admin') }}" class="za-btnPrimary">{{ __('ui.dashboard.go_backoffice') }}</a>
            <a href="{{ route('catalog') }}" class="za-btnOutline">{{ __('ui.nav.catalog') }}</a>
          </div>
        </div>

      {{-- TEACHER --}}
      @elseif($user && $user->hasRole('teacher'))
        <div class="za-roleCard mt-6">
          <div class="text-lg font-extrabold text-white">{{ __('ui.dashboard.teacher_title') }}</div>
          <p class="mt-2 text-sm text-white/70">
            {{ __('ui.dashboard.teacher_subtitle') }}
          </p>

          <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('my.courses') }}" class="za-btnPrimary">{{ __('ui.nav.my_courses') }}</a>
            <a href="{{ route('catalog') }}" class="za-btnOutline">{{ __('ui.nav.catalog') }}</a>
          </div>
        </div>

      {{-- STUDENT / DEFAULT --}}
      @else
        <div class="za-heroCard mt-6">
          <div class="za-heroLeft">
            <div class="za-heroBadge">Espace étudiant</div>
            <h2 class="za-heroTitle">Continuez votre progression en toute clarté</h2>
            <p class="za-heroText">
              Suivez vos packs, vos quiz et vos avancées depuis un seul espace premium.
            </p>

            @if($continueLabel && $continueLabel !== __('ui.nav.my_courses'))
              <div class="za-continueCard">
                <div class="za-continueTop">
                  <span class="za-continueKicker">À reprendre maintenant</span>

                  @if($continueType)
                    <span class="za-continueType">{{ $continueType }}</span>
                  @endif
                </div>

                <h3 class="za-continueTitle">{{ $continueLabel }}</h3>

                @if($continueMeta)
                  <p class="za-continueMeta">{{ $continueMeta }}</p>
                @endif

                <div class="za-continueBar">
                  <div class="za-continueBarFill" style="width: {{ $continueProgress }}%"></div>
                </div>

                <div class="za-continueProgressText">{{ $continueProgress }}% complété</div>
              </div>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
              <a href="{{ $continueUrl }}" class="za-btnPrimary">Continuer</a>
              <a href="{{ route('my.courses') }}" class="za-btnOutline">{{ __('ui.nav.my_courses') }}</a>
              <a href="{{ route('catalog') }}" class="za-btnOutline">{{ __('ui.nav.catalog') }}</a>
            </div>
          </div>

          <div class="za-heroRight">
            <div class="za-ringCard">
              <div class="za-ringValue">{{ (int) ($dashboardStats['average_progress'] ?? 0) }}%</div>
              <div class="za-ringLabel">Progression moyenne</div>
            </div>
          </div>
        </div>

        {{-- COMMANDES EN ATTENTE --}}
        @if($pendingOrders->isNotEmpty())
          <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-xl">⏳</span>
              <div>
                <div class="font-bold text-amber-900 text-sm">Demande(s) en attente de validation</div>
                <div class="text-xs text-amber-700">L'équipe Zaytouna va valider votre paiement. Vous recevrez une confirmation par email.</div>
              </div>
            </div>
            <div class="space-y-2">
              @foreach($pendingOrders as $order)
                <div class="flex items-center justify-between rounded-xl bg-white border border-amber-100 px-4 py-3">
                  <div>
                    <div class="text-sm font-semibold text-gray-800">
                      Commande #{{ $order->id }}
                      @if($order->items->isNotEmpty())
                        — {{ $order->items->map(fn($i) => $i->course?->title_display ?? $i->course?->title)->filter()->join(', ') }}
                      @endif
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $order->created_at->diffForHumans() }}</div>
                  </div>
                  <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                    En attente
                  </span>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <div class="za-statCard">
            <span class="za-statLabel">Produits actifs</span>
            <strong class="za-statValue">{{ $dashboardStats['active_products'] ?? 0 }}</strong>
          </div>

          <div class="za-statCard">
            <span class="za-statLabel">Quiz terminés</span>
            <strong class="za-statValue">{{ $dashboardStats['completed_quizzes'] ?? 0 }}</strong>
          </div>

          <div class="za-statCard">
            <span class="za-statLabel">Score moyen</span>
            <strong class="za-statValue">{{ (int) ($dashboardStats['average_score'] ?? 0) }}%</strong>
          </div>

          <div class="za-statCard">
            <span class="za-statLabel">Progression moyenne</span>
            <strong class="za-statValue">{{ (int) ($dashboardStats['average_progress'] ?? 0) }}%</strong>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 xl:grid-cols-12 gap-6">
          <div class="xl:col-span-7">
            <div class="za-panel">
              <div class="za-panelHead">
                <div>
                  <div class="za-panelKicker">Parcours</div>
                  <h3 class="za-panelTitle">Vos contenus en cours</h3>
                </div>
              </div>

              @if($learningProducts->count())
                <div class="mt-5 space-y-4">
                  @foreach($learningProducts as $product)
                    @php
                      $productTitle    = $product['title']           ?? 'Produit';
                      $productType     = $product['type_label']      ?? 'Course';
                      $productProgress = (int) ($product['progress'] ?? 0);
                      $productUrl      = $product['url']             ?? route('my.courses');
                      $productMeta     = $product['meta']            ?? null;
                      $quizzesCount    = (int) ($product['quizzes_count']   ?? 0);
                      $exercisesCount  = (int) ($product['exercises_count'] ?? 0);
                    @endphp

                    <div class="za-learningCard">
                      <div class="za-learningTop">
                        <div>
                          <div class="za-learningType">{{ $productType }}</div>
                          <h4 class="za-learningTitle">{{ $productTitle }}</h4>

                          @if($productMeta)
                            <p class="za-learningMeta">{{ $productMeta }}</p>
                          @endif

                          @if($quizzesCount > 0 || $exercisesCount > 0)
                            <div class="za-learningBadges">
                              @if($quizzesCount > 0)
                                <span class="za-learningBadge za-badgeQuiz">
                                  🎯 {{ $quizzesCount }} quiz
                                </span>
                              @endif
                              @if($exercisesCount > 0)
                                <span class="za-learningBadge za-badgeExercise">
                                  ✏️ {{ $exercisesCount }} exercice{{ $exercisesCount > 1 ? 's' : '' }}
                                </span>
                              @endif
                            </div>
                          @endif
                        </div>

                        <span class="za-learningProgressValue">{{ $productProgress }}%</span>
                      </div>

                      <div class="za-progressBar">
                        <div class="za-progressFill" style="width: {{ $productProgress }}%"></div>
                      </div>

                      <div class="mt-4">
                        <a href="{{ $productUrl }}" class="za-btnOutline">Continuer →</a>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="za-emptyState mt-5">
                  <div class="za-emptyTitle">{{ __('ui.dashboard.student_my_courses_title') }}</div>
                  <p class="za-emptyText">{{ __('ui.dashboard.student_my_courses_subtitle') }}</p>
                  <div class="mt-4">
                    <a href="{{ route('my.courses') }}" class="za-btnPrimary">{{ __('ui.dashboard.open') }}</a>
                  </div>
                </div>
              @endif
            </div>
          </div>

          <div class="xl:col-span-5 space-y-6">
            <div class="za-panel">
              <div class="za-panelHead">
                <div>
                  <div class="za-panelKicker">Activité</div>
                  <h3 class="za-panelTitle">Derniers quiz</h3>
                </div>
              </div>

              @if($recentQuizAttempts->count())
                <div class="mt-5 space-y-3">
                  @foreach($recentQuizAttempts as $attempt)
                    @php
                      $quizTitle = $attempt['title'] ?? 'Quiz';
                      $quizScore = (int) ($attempt['percentage'] ?? 0);
                      $quizStatus = $attempt['status'] ?? ($quizScore >= 50 ? 'Validé' : 'À renforcer');
                      $quizUrl = $attempt['url'] ?? null;
                    @endphp

                    <div class="za-quizItem">
                      <div>
                        <h4 class="za-quizTitle">{{ $quizTitle }}</h4>
                        <p class="za-quizMeta">{{ $quizStatus }}</p>
                      </div>

                      <div class="za-quizRight">
                        <span class="za-quizScore">{{ $quizScore }}%</span>

                        @if($quizUrl)
                          <a href="{{ $quizUrl }}" class="za-miniLink">Voir</a>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="za-emptyState mt-5">
                  <div class="za-emptyTitle">Aucun quiz récent</div>
                  <p class="za-emptyText">Vos derniers résultats apparaîtront ici dès que vous commencerez vos évaluations.</p>
                </div>
              @endif
            </div>

            <div class="za-panel">
              <div class="za-panelHead">
                <div>
                  <div class="za-panelKicker">Explorer</div>
                  <h3 class="za-panelTitle">{{ __('ui.dashboard.student_catalog_title') }}</h3>
                </div>
              </div>

              <p class="za-panelText mt-4">
                {{ __('ui.dashboard.student_catalog_subtitle') }}
              </p>

              <div class="mt-4">
                <a href="{{ route('catalog') }}" class="za-btnOutline">{{ __('ui.dashboard.explore') }}</a>
              </div>
            </div>
          </div>
        </div>
      @endif
    </x-container>

    <style>
      .za-dashboardPage{
        position:relative;
        min-height:calc(100vh - 80px);
        overflow:hidden;
        background:#08111d;
      }

      .za-dashboardBg{
        position:absolute; inset:0;
        background:
          radial-gradient(1200px 600px at 15% 10%, rgba(16,185,129,.14), transparent 60%),
          radial-gradient(900px 500px at 85% 20%, rgba(234,179,8,.10), transparent 55%),
          linear-gradient(180deg, #08111d 0%, #0b1726 100%);
      }

      .za-dashboardOverlay{
        position:absolute; inset:0;
        background:linear-gradient(to bottom, rgba(2,6,23,.35), rgba(2,6,23,.15), rgba(2,6,23,.35));
      }

      .za-roleCard,
      .za-heroCard,
      .za-statCard,
      .za-panel{
        border-radius:28px;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.06);
        backdrop-filter:blur(12px);
        box-shadow:0 22px 60px rgba(0,0,0,.30);
      }

      .za-roleCard,
      .za-panel,
      .za-statCard{
        padding:1.25rem;
      }

      .za-heroCard{
        padding:1.4rem;
        display:grid;
        grid-template-columns:1.5fr .8fr;
        gap:1.2rem;
        align-items:center;
      }

      .za-heroBadge{
        display:inline-flex;
        border-radius:999px;
        padding:.38rem .75rem;
        font-size:.82rem;
        font-weight:700;
        color:#ecfdf5;
        background:rgba(16,185,129,.14);
        border:1px solid rgba(16,185,129,.30);
      }

      .za-heroTitle{
        margin-top:.9rem;
        color:#fff;
        font-size:clamp(1.6rem, 3vw, 2.4rem);
        line-height:1.1;
        font-weight:900;
      }

      .za-heroText{
        margin-top:.8rem;
        color:rgba(255,255,255,.76);
        line-height:1.7;
        max-width:700px;
      }

      .za-continueCard{
        margin-top:1.1rem;
        border-radius:20px;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.05);
        padding:1rem;
        max-width:560px;
      }

      .za-continueTop{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        flex-wrap:wrap;
      }

      .za-continueKicker{
        color:rgba(234,179,8,.88);
        font-size:.78rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.05em;
      }

      .za-continueType{
        display:inline-flex;
        border-radius:999px;
        padding:.3rem .65rem;
        font-size:.76rem;
        font-weight:700;
        color:#d1fae5;
        background:rgba(16,185,129,.14);
        border:1px solid rgba(16,185,129,.28);
      }

      .za-continueTitle{
        margin-top:.75rem;
        color:#fff;
        font-size:1.05rem;
        font-weight:900;
        line-height:1.35;
      }

      .za-continueMeta{
        margin-top:.3rem;
        color:rgba(255,255,255,.72);
        font-size:.9rem;
      }

      .za-continueBar{
        margin-top:.9rem;
        height:10px;
        border-radius:999px;
        overflow:hidden;
        background:rgba(255,255,255,.08);
      }

      .za-continueBarFill{
        height:100%;
        border-radius:999px;
        background:linear-gradient(90deg, rgba(16,185,129,1), rgba(234,179,8,1));
      }

      .za-continueProgressText{
        margin-top:.45rem;
        color:rgba(255,255,255,.72);
        font-size:.82rem;
      }

      .za-heroRight{
        display:flex;
        justify-content:center;
      }

      .za-ringCard{
        width:180px;
        height:180px;
        border-radius:999px;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        background:
          radial-gradient(circle at center, rgba(255,255,255,.10) 0%, rgba(255,255,255,.04) 58%, rgba(16,185,129,.14) 100%);
        border:1px solid rgba(255,255,255,.10);
        box-shadow:0 0 0 14px rgba(16,185,129,.06);
      }

      .za-ringValue{
        color:#fff;
        font-size:2rem;
        font-weight:900;
      }

      .za-ringLabel{
        margin-top:.35rem;
        color:rgba(255,255,255,.74);
        font-size:.86rem;
        text-align:center;
      }

      .za-statLabel{
        display:block;
        color:rgba(255,255,255,.72);
        font-size:.88rem;
      }

      .za-statValue{
        display:block;
        margin-top:.4rem;
        color:#fff;
        font-size:1.6rem;
        font-weight:900;
      }

      .za-panelHead{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
      }

      .za-panelKicker{
        font-size:.8rem;
        font-weight:800;
        letter-spacing:.05em;
        text-transform:uppercase;
        color:rgba(234,179,8,.88);
        margin-bottom:.35rem;
      }

      .za-panelTitle{
        color:#fff;
        font-size:1.2rem;
        font-weight:900;
      }

      .za-panelText{
        color:rgba(255,255,255,.76);
        line-height:1.7;
      }

      .za-learningCard{
        border-radius:20px;
        border:1px solid rgba(255,255,255,.08);
        background:rgba(2,6,23,.24);
        padding:1rem;
      }

      .za-learningTop{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
      }

      .za-learningType{
        display:inline-flex;
        border-radius:999px;
        padding:.3rem .65rem;
        font-size:.76rem;
        font-weight:700;
        color:#d1fae5;
        background:rgba(16,185,129,.14);
        border:1px solid rgba(16,185,129,.28);
      }

      .za-learningTitle{
        margin-top:.75rem;
        color:#fff;
        font-size:1.05rem;
        font-weight:800;
        line-height:1.35;
      }

      .za-learningMeta{
        margin-top:.35rem;
        color:rgba(255,255,255,.72);
        font-size:.9rem;
      }

      .za-learningBadges{
        display:flex;
        flex-wrap:wrap;
        gap:.4rem;
        margin-top:.6rem;
      }

      .za-learningBadge{
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:.28rem .65rem;
        font-size:.76rem;
        font-weight:700;
      }

      .za-badgeQuiz{
        color:#bfdbfe;
        background:rgba(59,130,246,.14);
        border:1px solid rgba(59,130,246,.28);
      }

      .za-badgeExercise{
        color:#e9d5ff;
        background:rgba(139,92,246,.14);
        border:1px solid rgba(139,92,246,.28);
      }

      .za-learningProgressValue{
        color:#fff;
        font-size:1rem;
        font-weight:900;
      }

      .za-progressBar{
        margin-top:1rem;
        height:10px;
        border-radius:999px;
        overflow:hidden;
        background:rgba(255,255,255,.08);
      }

      .za-progressFill{
        height:100%;
        border-radius:999px;
        background:linear-gradient(90deg, rgba(16,185,129,1), rgba(234,179,8,1));
      }

      .za-quizItem{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        border-radius:18px;
        border:1px solid rgba(255,255,255,.08);
        background:rgba(2,6,23,.24);
        padding:.95rem 1rem;
      }

      .za-quizTitle{
        color:#fff;
        font-size:.98rem;
        font-weight:800;
        line-height:1.35;
      }

      .za-quizMeta{
        margin-top:.25rem;
        color:rgba(255,255,255,.72);
        font-size:.86rem;
      }

      .za-quizRight{
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:.35rem;
      }

      .za-quizScore{
        color:#fff;
        font-size:1rem;
        font-weight:900;
      }

      .za-miniLink{
        color:#fde68a;
        font-size:.82rem;
        font-weight:700;
      }

      .za-emptyState{
        border-radius:20px;
        border:1px dashed rgba(255,255,255,.14);
        background:rgba(255,255,255,.04);
        padding:1.1rem;
      }

      .za-emptyTitle{
        color:#fff;
        font-size:1rem;
        font-weight:800;
      }

      .za-emptyText{
        margin-top:.4rem;
        color:rgba(255,255,255,.72);
        line-height:1.65;
      }

      .za-btnPrimary,
      .za-btnOutline{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:16px;
        padding:.82rem 1.1rem;
        font-size:.92rem;
        font-weight:800;
        transition:.18s ease;
      }

      .za-btnPrimary{
        color:#07111d;
        background:linear-gradient(135deg, rgba(234,179,8,1), rgba(16,185,129,.92));
        box-shadow:0 14px 32px rgba(0,0,0,.25);
      }

      .za-btnOutline{
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.05);
      }

      .za-btnPrimary:hover,
      .za-btnOutline:hover{
        transform:translateY(-1px);
      }

      @media (max-width: 1024px){
        .za-heroCard{
          grid-template-columns:1fr;
        }

        .za-heroRight{
          justify-content:flex-start;
        }
      }

      @media (max-width: 768px){
        .za-roleCard,
        .za-heroCard,
        .za-statCard,
        .za-panel{
          border-radius:22px;
        }

        .za-btnPrimary,
        .za-btnOutline{
          width:100%;
          text-align:center;
        }
      }
    </style>
  </div>
@endsection