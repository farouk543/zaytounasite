{{-- resources/views/regimes/checkout.blade.php (example name) --}}
@extends('layouts.app')

@section('content')
@php
  $packKey = $selection['pack'] ?? null;

  $trackName = $track->name_i18n ?? $track->name ?? '—';

  $visCurrency = $visitorCurrency ?? 'TND';
  $packCatalog = [
    'individual' => [
      'title' => __('ui.packs.individual_title'),
      'price' => \App\Services\CurrencyService::formatPack('individual', $visCurrency),
      'badge' => __('ui.packs.recommended_badge'),
      'features' => [
        __('ui.packs.individual_f1'),
        __('ui.packs.individual_f2'),
        __('ui.packs.individual_f3'),
      ],
    ],
    'duo' => [
      'title' => __('ui.packs.duo_title'),
      'price' => \App\Services\CurrencyService::formatPack('duo', $visCurrency),
      'badge' => null,
      'features' => [
        __('ui.packs.duo_f1'),
        __('ui.packs.duo_f2'),
        __('ui.packs.duo_f3'),
      ],
    ],
    'group' => [
      'title' => __('ui.packs.group_title'),
      'price' => \App\Services\CurrencyService::formatPack('group', $visCurrency),
      'badge' => null,
      'features' => [
        __('ui.packs.group_f1'),
        __('ui.packs.group_f2'),
        __('ui.packs.group_f3'),
      ],
    ],
    'recorded' => [
      'title' => __('ui.packs.recorded_title'),
      'price' => \App\Services\CurrencyService::formatPack('recorded', $visCurrency, 'quarterly'),
      'badge' => null,
      'features' => [
        __('ui.packs.recorded_f1'),
        __('ui.packs.recorded_f2'),
        __('ui.packs.recorded_f3'),
        __('ui.packs.recorded_f4'),
      ],
    ],
  ];

  $isCustom = $packKey === 'custom';
  $pack = (!$isCustom && $packKey && isset($packCatalog[$packKey])) ? $packCatalog[$packKey] : null;

  // Classic fields
  $levelId = $selection['level_id'] ?? null;
  $subjectId = $selection['subject_id'] ?? null;
  $grade = $selection['grade'] ?? null;

  $levelName = null;
  $subjectName = null;

  // ✅ i18n names (and avoid querying inside view if you can later pass names from controller)
  if(!$isCustom){
    if ($levelId) {
      $lvl = \App\Models\Level::query()->select(['id','name','name_ar','name_en'])->find($levelId);
      $levelName = $lvl?->name_i18n ?? $lvl?->name;
    }
    if ($subjectId) {
      $sub = \App\Models\Subject::query()->select(['id','name','name_ar','name_en'])->find($subjectId);
      $subjectName = $sub?->name_i18n ?? $sub?->name;
    }
  }

  // Custom fields
  $plan = $selection['plan'] ?? null;
  $pricing = $selection['pricing'] ?? [];
  $items = $selection['items'] ?? [];
  $maps = $maps ?? ['levels'=>[], 'subjects'=>[]];

  $currency = $pricing['currency'] ?? $visCurrency;
  $symbol   = \App\Services\CurrencyService::SYMBOLS[$currency] ?? $currency;

  $money = function($cents) use ($symbol) {
    $v = ((int)$cents) / 100;
    return number_format($v, 2) . ' ' . $symbol;
  };

  $planLabel = match($plan){
    'monthly' => __('ui.packs.plan_monthly'),
    'quarterly' => __('ui.packs.plan_quarterly'),
    'yearly' => __('ui.packs.plan_yearly'),
    default => '—',
  };

  $missing = [];
  if (!$packKey) $missing[] = __('ui.packs.missing_pack');
  if (!$isCustom) {
    if (!$levelId) $missing[] = __('ui.packs.missing_level');
    if (!$subjectId) $missing[] = __('ui.packs.missing_subject');
  } else {
    if (empty($items)) $missing[] = __('ui.packs.missing_items');
    if (!$plan) $missing[] = __('ui.packs.missing_plan');
  }
@endphp

<section class="za-section za-sectionAlt">
  <div class="za-container">
    <div class="za-sectionHead" data-reveal>
      <div class="pill">{{ __('ui.checkout.pill') }}</div>
      <h1 class="za-h2">{{ __('ui.checkout.title') }}</h1>
      <p class="za-muted">
        {{ __('ui.checkout.subtitle') }}
      </p>
    </div>

    @if(session('error'))
      <div class="lux-card" style="padding:14px; border-left:4px solid #ef4444; margin-top:14px;">
        ❌ {{ session('error') }}
      </div>
    @endif

    @if(!empty($missing))
      <div class="lux-card" style="padding:14px; border-left:4px solid #f59e0b; margin-top:14px;">
        <strong>{{ __('ui.checkout.missing_title') }}</strong> {{ implode(', ', $missing) }}.
        <div class="za-muted" style="margin-top:6px;">
          {{ __('ui.checkout.missing_hint') }}
        </div>
      </div>
    @endif

    <div class="mt-10" style="display:grid; grid-template-columns:1fr; gap:16px;">

      {{-- SUMMARY --}}
      <div class="lux-card" style="padding:22px;" data-reveal>
        <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
          <div>
            <div class="lux-title">{{ __('ui.checkout.summary_title') }}</div>
            <div class="za-muted" style="margin-top:6px;">
              {{ __('ui.checkout.summary_hint') }}
            </div>
          </div>

          <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <span class="pill">{{ __('ui.checkout.test_mode') }}</span>
            <span class="za-muted" style="font-size:13px;">{{ __('ui.checkout.payment_soon') }}</span>
          </div>
        </div>

        <div style="margin-top:16px; display:grid; gap:10px; color:#0f172a;">
          <div><strong>{{ __('ui.checkout.track') }}</strong> {{ $trackName }}</div>

          @if(!$isCustom)
            <div><strong>{{ __('ui.checkout.pack') }}</strong> {{ $pack['title'] ?? ($packKey ?? '—') }}</div>
            <div><strong>{{ __('ui.checkout.level') }}</strong> {{ $levelName ?? ($levelId ?? '—') }}</div>
            <div><strong>{{ __('ui.checkout.grade') }}</strong> {{ $grade ?? '—' }}</div>
            <div><strong>{{ __('ui.checkout.subject') }}</strong> {{ $subjectName ?? ($subjectId ?? '—') }}</div>
          @else
            <div><strong>{{ __('ui.checkout.pack') }}</strong> {{ __('ui.packs.custom_title') }}</div>
            <div><strong>{{ __('ui.checkout.plan') }}</strong> {{ $planLabel }}</div>
            <div><strong>{{ __('ui.checkout.hour_price') }}</strong> {{ $pricing['hour_price'] ?? 30 }} {{ $symbol }} / {{ __('ui.checkout.per_hour') }}</div>
          @endif
        </div>

        <hr style="margin:16px 0; border:none; border-top:1px solid rgba(15,23,42,.08);">

        @if(!$isCustom)
          <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-end;">
            <div>
              <div class="za-muted">{{ __('ui.checkout.total') }}</div>
              <div style="font-size:22px; font-weight:900; margin-top:4px;">
                {{ $pack['price'] ?? '—' }}
              </div>
              <div class="za-muted" style="margin-top:6px; font-size:13px;">
                {{ __('ui.checkout.total_hint') }}
              </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
              <a class="btn-outline" href="{{ route('regimes.tunisia.show') }}">{{ __('ui.back') }}</a>
              <form method="POST" action="{{ route('regimes.tunisia.submit') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-primary" onclick="this.disabled=true;this.textContent='Envoi en cours…';">
                  ✅ Confirmer ma demande
                </button>
              </form>
            </div>
          </div>
        @else
          @php
            $subCents = $pricing['subtotal_cents'] ?? 0;
            $discCents = $pricing['discount_cents'] ?? 0;
            $totalCents = $pricing['total_cents'] ?? 0;
            $months = $pricing['months'] ?? 12;
            $rate = (float)($pricing['discount_rate'] ?? 0.2);
          @endphp

          <div class="lux-card" style="padding:16px; background:rgba(15,23,42,.03);">
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
              <div class="za-muted">
                <div>{{ __('ui.checkout.subtotal_for') }} ({{ $months }} {{ __('ui.checkout.months') }})</div>
                <div style="font-weight:900; color:#0f172a;">{{ $money($subCents) }}</div>
              </div>

              <div class="za-muted">
                <div>{{ __('ui.checkout.discount') }} ({{ (int)round($rate*100) }}%)</div>
                <div style="font-weight:900; color:#0f172a;">- {{ $money($discCents) }}</div>
              </div>

              <div>
                <div class="za-muted">{{ __('ui.checkout.total_to_pay') }}</div>
                <div style="font-size:22px; font-weight:900; color:var(--emerald-950);">
                  {{ $money($totalCents) }}
                </div>
              </div>
            </div>

            @if($plan === 'yearly')
              <div class="za-muted" style="margin-top:10px;">
                ⭐ {{ __('ui.checkout.yearly_reco') }} <strong>{{ $money($discCents) }}</strong>.
              </div>
            @endif
          </div>

          <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <a class="btn-outline" href="{{ route('regimes.tunisia.show') }}">{{ __('ui.back') }}</a>
            <form method="POST" action="{{ route('regimes.tunisia.submit') }}" style="display:inline;">
              @csrf
              <button type="submit" class="btn-primary" onclick="this.disabled=true;this.textContent='Envoi en cours…';">
                ✅ Confirmer ma demande
              </button>
            </form>
          </div>

          <div class="za-muted" style="margin-top:12px;">
            ✅ {{ __('ui.checkout.next_step_hint') }}
          </div>
        @endif
      </div>

      {{-- CUSTOM LINES TABLE --}}
      @if($isCustom)
        <div class="lux-card" style="padding:22px;" data-reveal>
          <div class="lux-title">{{ __('ui.checkout.custom_details_title') }}</div>
          <div class="za-muted" style="margin-top:6px;">
            {{ __('ui.checkout.custom_details_subtitle') }}
          </div>

          <div style="margin-top:14px; overflow:auto; border:1px solid rgba(15,23,42,.08); border-radius:18px;">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
              <thead>
                <tr style="background:rgba(15,23,42,.03); text-align:left;">
                  <th style="padding:12px;">{{ __('ui.checkout.table_child') }}</th>
                  <th style="padding:12px;">{{ __('ui.checkout.table_grade') }}</th>
                  <th style="padding:12px;">{{ __('ui.checkout.table_subject') }}</th>
                  <th style="padding:12px;">{{ __('ui.checkout.table_duration') }}</th>
                  <th style="padding:12px;">{{ __('ui.checkout.table_sessions') }}</th>
                  <th style="padding:12px;">{{ __('ui.checkout.table_monthly') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $it)
                  @php
                    $child = $it['child'] ?? '';
                    $lvlName = $maps['levels'][$it['level_id']] ?? ('#'.$it['level_id']);
                    $subName = $maps['subjects'][$it['subject_id']] ?? ('#'.$it['subject_id']);
                    $dur = $it['duration_h'] ?? 1;
                    $spm = $it['sessions_per_month'] ?? 0;
                    $lineMonthly = $it['line_monthly'] ?? 0;
                  @endphp
                  <tr style="border-top:1px solid rgba(15,23,42,.08);">
                    <td style="padding:12px;">{{ $child ?: '—' }}</td>
                    <td style="padding:12px;">{{ $lvlName }}</td>
                    <td style="padding:12px;">{{ $subName }}</td>
                    <td style="padding:12px;">{{ $dur }}h</td>
                    <td style="padding:12px;">{{ $spm }}</td>
                    <td style="padding:12px; font-weight:900;">
                      {{ number_format((float)$lineMonthly, 0) }} {{ $symbol }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="za-muted" style="margin-top:10px;">
            {{ __('ui.checkout.monthly_subtotal') }} <strong>{{ $money($pricing['subtotal_monthly_cents'] ?? 0) }}</strong>
          </div>
        </div>
      @endif

      {{-- PACK FEATURES (classic packs only) --}}
      @if(!$isCustom)
        <div class="lux-card" style="padding:22px;" data-reveal>
          <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
              <div class="lux-title">{{ __('ui.checkout.what_you_get') }}</div>
              <div class="za-muted" style="margin-top:6px;">
                {{ __('ui.checkout.what_you_get_hint') }}
              </div>
            </div>

            @if($pack && !empty($pack['badge']))
              <span class="pill">{{ $pack['badge'] }}</span>
            @endif
          </div>

          @if($pack)
            <ul class="za-muted" style="margin-top:14px; display:grid; gap:8px;">
              @foreach($pack['features'] as $f)
                <li>✅ {{ $f }}</li>
              @endforeach
            </ul>
          @else
            <div class="za-muted" style="margin-top:14px;">
              {{ __('ui.checkout.no_pack_selected') }}
            </div>
          @endif
        </div>
      @endif

      {{-- NEXT STEPS --}}
      <div class="lux-card" style="padding:22px;" data-reveal>
        <div class="lux-title">{{ __('ui.checkout.roadmap_title') }}</div>
        <ol class="za-muted" style="margin-top:10px; display:grid; gap:8px; padding-left:18px;">
          <li>{{ __('ui.checkout.roadmap_1') }}</li>
          <li>{{ __('ui.checkout.roadmap_2') }}</li>
          <li>{{ __('ui.checkout.roadmap_3') }}</li>
          <li>{{ __('ui.checkout.roadmap_4') }}</li>
        </ol>
      </div>

    </div>
  </div>
</section>

<div data-reveal>
  <x-footer-premium />
</div>
@endsection