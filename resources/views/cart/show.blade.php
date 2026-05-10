@extends('layouts.app')

@section('content')
  @php
    use Illuminate\Support\Facades\Storage;

    $isRtl = app()->getLocale() === 'ar';

    $fmt = function(int $cents) {
      return number_format($cents / 100, 2);
    };

    $currency = $totals['currency'] ?? 'TND';
    $subtotal = (int)($totals['subtotal_cents'] ?? 0);
    $total = (int)($totals['total_cents'] ?? $subtotal);

    $thumbUrl = function ($item) {
      $type = $item['type'] ?? 'course';

      if ($type === 'course') {
        $course = $item['course'] ?? null;
        $p = $course?->thumbnail_path ?? null;

        if (! $p) {
          return asset('assets/defaults/course-thumb.svg');
        }

        if (str_starts_with($p, 'public/')) {
          $p = substr($p, 7);
        }

        $p = ltrim($p, '/');

        return Storage::disk('public')->url($p);
      }

      if ($type === 'exercise') {
        $exercise = $item['exercise'] ?? null;

        if ($exercise?->video_path) {
          return asset('assets/defaults/video-thumb.svg');
        }

        if ($exercise?->audio_path) {
          return asset('assets/defaults/audio-thumb.svg');
        }

        if ($exercise?->pdf_path) {
          return asset('assets/defaults/pdf-thumb.svg');
        }

        return asset('assets/defaults/exercise-thumb.svg');
      }

      return asset('assets/defaults/course-thumb.svg');
    };

    $itemTitle = function ($item) {
      if (($item['type'] ?? 'course') === 'course') {
        $course = $item['course'] ?? null;

        return $course?->title_i18n
          ?? $course?->title_display
          ?? $course?->title
          ?? $item['title']
          ?? 'Cours';
      }

      $exercise = $item['exercise'] ?? null;

      return $exercise?->title_i18n
        ?? $exercise?->title_display
        ?? $exercise?->title
        ?? $item['title']
        ?? 'Exercice';
    };

    $itemUrl = function ($item) {
      if (($item['type'] ?? 'course') === 'course') {
        $course = $item['course'] ?? null;

        return $course ? route('courses.show', $course) : route('catalog');
      }

      $exercise = $item['exercise'] ?? null;

      return $exercise ? route('exercise.show', $exercise) : route('catalog', ['type' => 'exercise']);
    };

    $itemCurrency = function ($item) {
      return $item['currency'] ?? 'TND';
    };

    $itemBadge = function ($item) {
      if (($item['type'] ?? 'course') === 'course') {
        $course = $item['course'] ?? null;

        if ($course && method_exists($course, 'isPack') && $course->isPack()) {
          return __('ui.type_live_pack') ?? 'Pack de cours';
        }

        return __('ui.type_course') ?? 'Cours';
      }

      $exercise = $item['exercise'] ?? null;

      return match($exercise?->exercise_type) {
        'pdf' => 'Exercice PDF',
        'audio_interactive' => 'Exercice audio',
        'video_interactive' => 'Exercice vidéo',
        'interactive' => 'Exercice interactif',
        default => 'Exercice',
      };
    };
  @endphp

  <div class="bg-gradient-to-b from-white to-gray-50">
    <x-container class="py-10">

      {{-- Header --}}
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
          <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-700">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            <span>{{ __('ui.cart.label') ?? 'Panier' }}</span>
          </div>

          <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
            {{ __('ui.cart_title') ?? 'Votre panier' }}
          </h1>

          <p class="mt-2 text-gray-600">
            {{ __('ui.cart_subtitle') ?? 'Paiement sécurisé — Accès privé lié à votre compte après paiement.' }}
          </p>
        </div>

        <a href="{{ route('catalog') }}" class="text-sm font-semibold text-gray-900 hover:underline">
          ← {{ __('ui.back_catalog') ?? 'Retour au catalogue' }}
        </a>
      </div>

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-900">
          {{ session('error') }}
        </div>
      @endif

      {{-- Empty state --}}
      @if(empty($items))
        <div class="mt-8 rounded-3xl border border-gray-200 bg-white p-8">
          <div class="text-lg font-bold text-gray-900">
            {{ __('ui.cart_empty') ?? 'Votre panier est vide.' }}
          </div>

          <p class="mt-2 text-gray-600">
            {{ __('ui.cart_empty_hint') ?? 'Ajoutez un cours ou un exercice depuis le catalogue pour continuer.' }}
          </p>

          <div class="mt-6">
            <a href="{{ route('catalog') }}" class="btn-primary inline-flex">
              {{ __('ui.browse_courses') ?? 'Voir le catalogue' }}
            </a>
          </div>
        </div>
      @else

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6">

          {{-- Items --}}
          <div class="lg:col-span-8">
            <div class="rounded-3xl border border-gray-200 bg-white overflow-hidden">
              <div class="relative p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-sky-400 to-emerald-400 opacity-80"></div>

                <div class="flex items-center justify-between">
                  <div class="text-lg font-extrabold text-gray-900">
                    {{ __('ui.cart_items') ?? 'Contenus sélectionnés' }}
                  </div>

                  <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                      {{ __('ui.cart_clear') ?? 'Vider le panier' }}
                    </button>
                  </form>
                </div>

                <div class="mt-5 space-y-4">
                  @foreach($items as $it)
                    @php
                      $type = $it['type'] ?? 'course';

                      $course = $it['course'] ?? null;
                      $exercise = $it['exercise'] ?? null;

                      $title = $itemTitle($it);
                      $url = $itemUrl($it);
                      $badge = $itemBadge($it);

                      $line = (int)($it['line_total_cents'] ?? 0);
                      $priceCents = (int)($it['price_cents'] ?? 0);
                      $itemCur = $itemCurrency($it);

                      $img = $thumbUrl($it);

                      $trackName = null;
                      $levelName = null;
                      $subjectName = null;

                      if ($type === 'course' && $course) {
                        $trackName = $course->subject?->level?->track?->name_i18n ?? $course->subject?->level?->track?->name;
                        $levelName = $course->subject?->level?->name_i18n ?? $course->subject?->level?->name;
                        $subjectName = $course->subject?->name_i18n ?? $course->subject?->name;
                      }

                      $removeRoute = $it['remove_route'] ?? null;
                    @endphp

                    <div class="rounded-2xl border border-gray-200 p-5 hover:shadow-sm transition">
                      <div class="flex items-start gap-4">
                        {{-- Thumb --}}
                        <div class="shrink-0">
                          <div class="h-16 w-24 rounded-2xl overflow-hidden border border-gray-200 bg-gray-50">
                            <img
                              src="{{ $img }}"
                              alt=""
                              class="h-full w-full object-cover"
                              loading="lazy"
                            />
                          </div>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                          <div class="za-cartItemInner flex items-start justify-between gap-3">
                            <div class="min-w-0">
                              <a href="{{ $url }}"
                                 class="block text-base font-bold text-gray-900 hover:underline truncate">
                                {{ $title }}
                              </a>

                              @if($type === 'course')
                                <div class="mt-1 text-sm text-gray-600">
                                  @if($trackName) {{ $trackName }} @endif
                                  @if($levelName) • {{ $levelName }} @endif
                                  @if($subjectName) • {{ $subjectName }} @endif
                                </div>
                              @else
                                <div class="mt-1 text-sm text-gray-600">
                                  Exercice indépendant
                                  @if($exercise?->estimated_duration_minutes)
                                    • {{ $exercise->estimated_duration_minutes }} min
                                  @endif
                                </div>
                              @endif

                              <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full px-3 py-1 {{ $type === 'exercise' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                  {{ $badge }}
                                </span>

                                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                                  {{ $fmt($priceCents) }} {{ $itemCur }}
                                </span>

                                @if($type === 'exercise' && $exercise?->audio_path)
                                  <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">
                                    Audio
                                  </span>
                                @endif

                                @if($type === 'exercise' && $exercise?->video_path)
                                  <span class="rounded-full bg-purple-50 px-3 py-1 text-purple-700">
                                    Vidéo
                                  </span>
                                @endif

                                @if($type === 'exercise' && $exercise?->pdf_path)
                                  <span class="rounded-full bg-red-50 px-3 py-1 text-red-700">
                                    PDF
                                  </span>
                                @endif
                              </div>
                            </div>

                            {{-- Price --}}
                            <div class="text-right shrink-0">
                              <div class="text-sm text-gray-600">{{ __('ui.total') ?? 'Total' }}</div>
                              <div class="text-lg font-extrabold text-gray-900">
                                {{ $fmt($line) }} {{ $itemCur }}
                              </div>
                            </div>
                          </div>

                          {{-- Actions --}}
                          <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="text-xs text-gray-500">
                              🔒 {{ __('ui.private_access_note') ?? 'Accès privé après paiement, lié à votre compte.' }}
                            </div>

                            @if($removeRoute)
                              <form method="POST" action="{{ $removeRoute }}">
                                @csrf
                                <button type="submit"
                                        class="text-sm font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                                  {{ __('ui.remove') ?? 'Retirer' }}
                                </button>
                              </form>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

              </div>
            </div>
          </div>

          {{-- Summary --}}
          <div class="lg:col-span-4">
            <div class="sticky top-6 rounded-3xl border border-gray-200 bg-white p-6">
              <div class="text-lg font-extrabold text-gray-900">
                {{ __('ui.order_summary') ?? 'Résumé de commande' }}
              </div>

              <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">{{ __('ui.subtotal') ?? 'Sous-total' }}</span>
                  <span class="font-semibold text-gray-900">{{ $fmt($subtotal) }} {{ $currency }}</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">{{ __('ui.fees') ?? 'Frais' }}</span>
                  <span class="font-semibold text-gray-900">0.00 {{ $currency }}</span>
                </div>

                <div class="h-px bg-gray-200"></div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-900 font-bold">{{ __('ui.total') ?? 'Total' }}</span>
                  <span class="text-gray-900 font-extrabold text-xl">{{ $fmt($total) }} {{ $currency }}</span>
                </div>
              </div>

              <div class="mt-5 space-y-2">
                {{-- Formulaire caché — soumis par le modal --}}
                <form id="za-manualForm" method="POST" action="{{ route('checkout.manual') }}" class="hidden">
                  @csrf
                </form>

                <button
                  type="button"
                  onclick="zaOpenPayModal()"
                  class="btn-primary za-btnBlock"
                >
                  {{ __('ui.pay_now') ?? 'Payer maintenant' }}
                </button>

                <a href="{{ route('catalog') }}" class="btn-outline za-btnBlock">
                  {{ __('ui.continue_shopping') ?? 'Continuer le shopping' }}
                </a>
              </div>

              <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800">
                <div class="font-semibold">✅ {{ __('ui.after_payment') ?? 'Après paiement' }}</div>
                <ul class="mt-1 list-disc pl-5">
                  <li>Les cours apparaissent dans Mon Profil → Mes cours après validation.</li>
                  <li>Les exercices achetés seront accessibles avec votre compte.</li>
                  <li>L’équipe Zaytouna valide votre demande manuellement.</li>
                </ul>
              </div>
            </div>
          </div>

        </div>
      @endif

    </x-container>
  </div>

  {{-- Modal de confirmation --}}
  <div id="za-payModal" class="za-modalBackdrop" onclick="zaClosePayModal(this)">
    <div class="za-modalBox" onclick="event.stopPropagation()">

      <div class="za-modalBg"></div>

      <div class="za-modalContent">
        {{-- Header --}}
        <div class="za-modalHeader">
          <div>
            <div class="za-modalKicker">Confirmation de commande</div>
            <h2 class="za-modalTitle">Envoyer votre demande</h2>
          </div>
          <button type="button" onclick="zaClosePayModal()" class="za-modalClose">✕</button>
        </div>

        {{-- Résumé --}}
        <div class="za-modalSummary">
          @foreach($items as $it)
            @php
              $title = $itemTitle($it);
              $itemCur = $itemCurrency($it);
            @endphp

            <div class="za-modalItem">
              <span class="za-modalItemName">{{ $title }}</span>
              <span class="za-modalItemPrice">
                {{ number_format(($it['price_cents'] ?? 0) / 100, 2) }} {{ $itemCur }}
              </span>
            </div>
          @endforeach

          <div class="za-modalDivider"></div>

          <div class="za-modalTotal">
            <span>Total</span>
            <strong>{{ number_format($total / 100, 2) }} {{ $currency }}</strong>
          </div>
        </div>

        {{-- Message --}}
        <div class="za-modalInfo">
          <div class="za-modalInfoIcon">📋</div>
          <p class="za-modalInfoText">
            Votre demande sera transmise à l'équipe <strong>Zaytouna Academy</strong>.
            Votre accès aux cours et exercices sera activé après confirmation manuelle sous 24h.
          </p>
        </div>

        {{-- Actions --}}
        <div class="za-modalActions">
          <button type="button" onclick="zaClosePayModal()" class="za-modalBtnOutline">
            Annuler
          </button>
          <button type="button" onclick="zaConfirmManualPay()" class="za-modalBtnPrimary">
            ✅ Confirmer la demande
          </button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .za-btnBlock{
      display:block;
      width:100%;
      text-align:center;
    }

    .za-modalBackdrop{
      display:none;
      position:fixed;
      inset:0;
      z-index:9999;
      background:rgba(2,6,23,.72);
      backdrop-filter:blur(6px);
      align-items:center;
      justify-content:center;
      padding:1rem;
    }

    .za-modalBackdrop.is-open{
      display:flex;
    }

    .za-modalBox{
      position:relative;
      width:100%;
      max-width:480px;
      border-radius:28px;
      border:1px solid rgba(255,255,255,.12);
      overflow:hidden;
      box-shadow:0 32px 80px rgba(0,0,0,.55);
      animation:zaModalIn .22s ease;
    }

    @keyframes zaModalIn{
      from{ opacity:0; transform:translateY(18px) scale(.97); }
      to{ opacity:1; transform:translateY(0) scale(1); }
    }

    .za-modalBg{
      position:absolute;
      inset:0;
      background:
        radial-gradient(800px 400px at 10% 0%, rgba(16,185,129,.18), transparent 60%),
        radial-gradient(600px 300px at 90% 100%, rgba(234,179,8,.12), transparent 55%),
        linear-gradient(160deg, #0d1f2f 0%, #08111d 100%);
    }

    .za-modalContent{
      position:relative;
      z-index:2;
      padding:1.8rem;
    }

    .za-modalHeader{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:1rem;
    }

    .za-modalKicker{
      font-size:.78rem;
      font-weight:800;
      letter-spacing:.05em;
      text-transform:uppercase;
      color:rgba(234,179,8,.88);
      margin-bottom:.35rem;
    }

    .za-modalTitle{
      color:#fff;
      font-size:1.4rem;
      font-weight:900;
      line-height:1.2;
    }

    .za-modalClose{
      color:rgba(255,255,255,.55);
      font-size:1.1rem;
      line-height:1;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.10);
      border-radius:999px;
      width:32px;
      height:32px;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      flex-shrink:0;
      transition:.15s ease;
    }

    .za-modalClose:hover{
      background:rgba(255,255,255,.10);
      color:#fff;
    }

    .za-modalSummary{
      margin-top:1.4rem;
      border-radius:18px;
      border:1px solid rgba(255,255,255,.08);
      background:rgba(255,255,255,.04);
      padding:1rem;
    }

    .za-modalItem{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:1rem;
      padding:.5rem 0;
    }

    .za-modalItemName{
      color:rgba(255,255,255,.9);
      font-size:.92rem;
      font-weight:600;
      min-width:0;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
    }

    .za-modalItemPrice{
      color:#fff;
      font-size:.92rem;
      font-weight:800;
      flex-shrink:0;
    }

    .za-modalDivider{
      height:1px;
      background:rgba(255,255,255,.10);
      margin:.6rem 0;
    }

    .za-modalTotal{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:1rem;
    }

    .za-modalTotal span{
      color:rgba(255,255,255,.72);
      font-size:.9rem;
    }

    .za-modalTotal strong{
      color:#fff;
      font-size:1.1rem;
      font-weight:900;
    }

    .za-modalInfo{
      margin-top:1.2rem;
      display:flex;
      align-items:flex-start;
      gap:.85rem;
      border-radius:16px;
      border:1px solid rgba(16,185,129,.22);
      background:rgba(16,185,129,.07);
      padding:.9rem 1rem;
    }

    .za-modalInfoIcon{
      font-size:1.1rem;
      flex-shrink:0;
      margin-top:.05rem;
    }

    .za-modalInfoText{
      color:rgba(255,255,255,.82);
      font-size:.88rem;
      line-height:1.65;
    }

    .za-modalInfoText strong{
      color:#ecfdf5;
    }

    .za-modalActions{
      margin-top:1.4rem;
      display:flex;
      gap:.75rem;
      flex-wrap:wrap;
    }

    .za-modalBtnOutline,
    .za-modalBtnPrimary{
      flex:1;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:16px;
      padding:.9rem 1rem;
      font-size:.92rem;
      font-weight:800;
      cursor:pointer;
      transition:.18s ease;
      border:none;
    }

    .za-modalBtnOutline{
      color:#fff;
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.12);
    }

    .za-modalBtnOutline:hover{
      background:rgba(255,255,255,.11);
    }

    .za-modalBtnPrimary{
      color:#07111d;
      background:linear-gradient(135deg, rgba(234,179,8,1), rgba(16,185,129,.95));
      box-shadow:0 12px 28px rgba(0,0,0,.30);
    }

    .za-modalBtnPrimary:hover{
      filter:brightness(1.04);
      transform:translateY(-1px);
    }

    @media (max-width: 480px){
      .za-modalActions{
        flex-direction:column;
      }

      .za-modalBtnOutline,
      .za-modalBtnPrimary{
        flex:none;
        width:100%;
      }
    }
  </style>

  <script>
    function zaOpenPayModal() {
      document.getElementById('za-payModal').classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function zaClosePayModal(el) {
      if (el && el.id !== 'za-payModal') return;

      document.getElementById('za-payModal').classList.remove('is-open');
      document.body.style.overflow = '';
    }

    function zaConfirmManualPay() {
      var btn = document.querySelector('.za-modalBtnPrimary');

      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Envoi en cours…';
      }

      document.getElementById('za-manualForm').submit();
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        zaClosePayModal();
      }
    });
  </script>
@endsection