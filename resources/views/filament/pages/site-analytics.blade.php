<x-filament-panels::page>
@php
  $fmt = fn ($n) => number_format((float) $n, is_float($n) && floor($n) != $n ? 1 : 0, ',', ' ');
  $flag = fn (?string $cc) => $cc && strlen($cc) === 2
      ? mb_chr(0x1F1E6 + ord(strtoupper($cc[0])) - 65).mb_chr(0x1F1E6 + ord(strtoupper($cc[1])) - 65)
      : '🌐';
  $country = function (?string $cc) {
      if (! $cc) return 'Inconnu';
      $name = class_exists(\Locale::class) ? \Locale::getDisplayRegion('-'.$cc, 'fr') : $cc;
      return $name && $name !== $cc ? $name : $cc;
  };
  $deviceLabels = ['desktop' => '🖥️ Ordinateur', 'mobile' => '📱 Mobile', 'tablet' => '📲 Tablette'];
  $localeLabels = ['fr' => '🇫🇷 Français', 'ar' => '🇹🇳 Arabe', 'en' => '🇬🇧 Anglais'];
  $kindLabels = ['link' => 'Lien', 'button' => 'Bouton', 'external' => 'Lien externe', 'cta' => 'Bouton suivi'];
  $maxTimeline = max(1, $timeline['rows']->max('pageviews'));
  $maxHour = max(1, $hours->max('views'));

  $kpiCards = [
    ['visitors', 'Visiteurs uniques', 'Personnes différentes venues sur le site.', false],
    ['pageviews', 'Pages vues', 'Nombre total de pages affichées.', false],
    ['pages_per_visitor', 'Pages / visiteur', 'Plus c’est haut, plus le site intéresse.', false],
    ['bounce', 'Rebond', 'Visiteurs partis après une seule page (%). Plus bas = mieux.', true],
    ['signups', 'Inscriptions', 'Nouveaux comptes créés.', false],
    ['conversion', 'Conversion', 'Inscriptions ÷ visiteurs (%).', false],
    ['orders', 'Commandes', 'Demandes de commande (cours, live).', false],
    ['paid', 'Commandes payées', 'Commandes validées / payées.', false],
  ];
@endphp

<style>
  .zs{display:flex;flex-direction:column;gap:1.25rem}
  .zs-card{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:16px;padding:1.1rem 1.2rem;box-shadow:0 1px 3px rgba(0,0,0,.04)}
  .dark .zs-card{background:rgba(255,255,255,.03);border-color:rgba(255,255,255,.08)}
  .zs-h{font-weight:800;font-size:.95rem;margin:0 0 .2rem;display:flex;align-items:center;gap:.5rem}
  .zs-sub{font-size:.76rem;opacity:.6;margin:0 0 .9rem}
  .zs-top{display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between}
  .zs-pills{display:flex;flex-wrap:wrap;gap:.4rem}
  .zs-pill{border-radius:999px;padding:.4rem .85rem;font-size:.8rem;font-weight:700;border:1px solid rgba(15,23,42,.12);background:transparent;cursor:pointer}
  .dark .zs-pill{border-color:rgba(255,255,255,.14)}
  .zs-pill.is-on{background:#d97706;border-color:#d97706;color:#fff}
  .zs-live{display:inline-flex;align-items:center;gap:.5rem;font-size:.85rem;font-weight:700}
  .zs-dot{width:9px;height:9px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 0 rgba(22,163,74,.6);animation:zsPulse 1.8s infinite}
  @keyframes zsPulse{70%{box-shadow:0 0 0 8px rgba(22,163,74,0)}100%{box-shadow:0 0 0 0 rgba(22,163,74,0)}}
  .zs-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.9rem}
  @media(max-width:1100px){.zs-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
  .zs-kpi .zs-label{font-size:.78rem;font-weight:700;opacity:.65}
  .zs-kpi .zs-val{font-size:1.75rem;font-weight:900;line-height:1.15;margin:.25rem 0}
  .zs-kpi .zs-hint{font-size:.7rem;opacity:.5}
  .zs-delta{font-size:.75rem;font-weight:800;border-radius:999px;padding:.1rem .5rem;margin-inline-start:.35rem;vertical-align:middle}
  .zs-up{background:rgba(22,163,74,.12);color:#15803d}
  .zs-down{background:rgba(220,38,38,.12);color:#b91c1c}
  .zs-flat{background:rgba(100,116,139,.12);color:#64748b}
  .zs-grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.25rem}
  .zs-grid3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.25rem}
  @media(max-width:1100px){.zs-grid2,.zs-grid3{grid-template-columns:1fr}}
  .zs-chart{display:flex;align-items:flex-end;gap:3px;height:190px;padding-top:.5rem;border-bottom:1px solid rgba(100,116,139,.25)}
  .zs-col{flex:1;display:flex;align-items:flex-end;justify-content:center;height:100%;position:relative;min-width:3px}
  .zs-bar-pv{position:absolute;bottom:0;left:15%;right:15%;background:rgba(217,119,6,.22);border-radius:4px 4px 0 0}
  .zs-bar-v{position:relative;width:70%;background:linear-gradient(180deg,#f59e0b,#d97706);border-radius:4px 4px 0 0;min-height:1px}
  .zs-col:hover .zs-bar-v{filter:brightness(1.15)}
  .zs-axis{display:flex;justify-content:space-between;font-size:.7rem;opacity:.55;margin-top:.35rem}
  .zs-legend{display:flex;gap:1rem;font-size:.75rem;opacity:.75}
  .zs-legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-inline-end:.3rem;vertical-align:-1px}
  .zs-table{width:100%;border-collapse:collapse;font-size:.83rem}
  .zs-table th{text-align:start;font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;opacity:.55;font-weight:800;padding:.35rem .4rem;border-bottom:1px solid rgba(100,116,139,.2)}
  .zs-table td{padding:.45rem .4rem;border-bottom:1px solid rgba(100,116,139,.1);vertical-align:top}
  .zs-table td.n,.zs-table th.n{text-align:end;white-space:nowrap;font-variant-numeric:tabular-nums}
  .zs-path{font-size:.72rem;opacity:.55;word-break:break-all}
  .zs-title{font-weight:700}
  .zs-row{display:grid;grid-template-columns:1fr auto;gap:.6rem;align-items:center;font-size:.83rem;padding:.3rem 0}
  .zs-meter{grid-column:1/-1;height:6px;border-radius:99px;background:rgba(100,116,139,.15);overflow:hidden;margin-top:-.15rem}
  .zs-meter span{display:block;height:100%;background:linear-gradient(90deg,#f59e0b,#d97706);border-radius:99px}
  .zs-empty{font-size:.82rem;opacity:.55;padding:.8rem 0;text-align:center}
  .zs-zero{color:#b91c1c;font-weight:800}
  .zs-hours{display:flex;align-items:flex-end;gap:2px;height:90px}
  .zs-hours div{flex:1;background:rgba(217,119,6,.75);border-radius:3px 3px 0 0;min-height:1px}
  .zs-tip{font-size:.78rem;background:rgba(217,119,6,.08);border:1px dashed rgba(217,119,6,.35);border-radius:12px;padding:.7rem .9rem;line-height:1.5}
  .zs-goalGrid{display:grid;grid-template-columns:230px minmax(0,1fr);gap:1.4rem;margin-top:1rem;align-items:start}
  @media(max-width:1100px){.zs-goalGrid{grid-template-columns:1fr}}
  .zs-scoreBox{display:flex;flex-direction:column;align-items:center;gap:.45rem;padding:1rem;border-radius:16px;background:rgba(100,116,139,.06)}
  .zs-ring{width:132px;height:132px;border-radius:50%;display:grid;place-items:center;align-content:center;
    background:radial-gradient(closest-side,var(--zs-bg,#fff) 78%,transparent 79%),conic-gradient(var(--c) calc(var(--p)*1%),rgba(100,116,139,.18) 0)}
  .dark .zs-ring{--zs-bg:#18181b}
  .zs-ring span{font-size:2.3rem;font-weight:900;line-height:1}
  .zs-ring small{font-size:.75rem;opacity:.55}
  .zs-verdict{font-weight:800;text-align:center;font-size:.9rem}
  .zs-badge{display:inline-block;border-radius:999px;padding:.15rem .55rem;font-size:.72rem;font-weight:800;white-space:nowrap}
  .zs-badge--success{background:rgba(22,163,74,.12);color:#15803d}
  .zs-badge--info{background:rgba(2,132,199,.12);color:#0369a1}
  .zs-badge--warning{background:rgba(217,119,6,.14);color:#b45309}
  .zs-badge--danger{background:rgba(220,38,38,.12);color:#b91c1c}
  .zs-badge--gray{background:rgba(100,116,139,.14);color:#64748b}
  .zs-meter span.zs-meter--success{background:linear-gradient(90deg,#22c55e,#16a34a)}
  .zs-meter span.zs-meter--warning{background:linear-gradient(90deg,#f59e0b,#d97706)}
  .zs-meter span.zs-meter--danger{background:linear-gradient(90deg,#f87171,#dc2626)}
  .zs-meter span.zs-meter--gray{background:#94a3b8}
  .zs-tips{margin-top:1.1rem;display:flex;flex-direction:column;gap:.45rem}
  .zs-tipRow{font-size:.82rem;line-height:1.5;padding:.55rem .8rem;border-radius:10px;background:rgba(100,116,139,.06)}
  .zs-tipRow--danger{background:rgba(220,38,38,.06)}
  .zs-tipRow--warning{background:rgba(217,119,6,.07)}
  .zs-tipRow--success{background:rgba(22,163,74,.07)}
  .zs-history{display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;margin-top:1rem}
  .zs-focus{text-align:end}
  .zs-focus strong{display:block;font-size:1.6rem;font-weight:900;color:#d97706;line-height:1}
  .zs-focus span{font-size:.72rem;opacity:.6}
  .zs-markets{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.8rem;margin-top:1rem}
  @media(max-width:1300px){.zs-markets{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media(max-width:700px){.zs-markets{grid-template-columns:repeat(2,minmax(0,1fr))}}
  .zs-market{border:1px solid rgba(100,116,139,.18);border-radius:14px;padding:.8rem}
  .zs-market.is-other{opacity:.7;border-style:dashed}
  .zs-marketHead{display:flex;gap:.4rem;align-items:center;font-size:.85rem}
  .zs-marketFlag{font-size:1.3rem}
  .zs-marketVal{font-size:1.5rem;font-weight:900;margin-top:.35rem}
  .zs-marketSub{font-size:.7rem;opacity:.55;margin-bottom:.4rem}
  .zs-market dl{display:grid;grid-template-columns:repeat(3,1fr);gap:.2rem;margin:.6rem 0 0;text-align:center}
  .zs-market dt{font-size:.62rem;opacity:.55;text-transform:uppercase;letter-spacing:.03em}
  .zs-market dd{margin:0;font-weight:800;font-size:.85rem}
  .zs-a{color:#d97706;text-decoration:none}
  .zs-a:hover{text-decoration:underline}
</style>

<div class="zs" wire:loading.class="opacity-60">

  {{-- Période --}}
  <div class="zs-top">
    <div class="zs-pills">
      @foreach($periods as $key => $label)
        <button type="button" wire:click="setPeriod('{{ $key }}')" class="zs-pill {{ $period === $key ? 'is-on' : '' }}">{{ $label }}</button>
      @endforeach
    </div>
    <div class="zs-live" title="Visiteurs actifs ces 5 dernières minutes">
      <span class="zs-dot"></span> {{ $live }} visiteur{{ $live > 1 ? 's' : '' }} en ce moment
    </div>
  </div>

  <div class="zs-tip">
    Du <strong>{{ $report->from->format('d/m/Y') }}</strong> au <strong>{{ $report->to->format('d/m/Y') }}</strong>,
    comparé à la période précédente. Les administrateurs et les robots (Google, etc.) ne sont pas comptés.
    Aucune adresse IP n’est stockée et aucun cookie n’est utilisé.
  </div>

  {{-- ===== Objectifs du mois & note de stratégie ===== --}}
  @php
    $goalsUrl = \App\Filament\Resources\MarketingGoals\MarketingGoalResource::getUrl('index');
    $scoreColors = ['success' => '#16a34a', 'info' => '#0284c7', 'warning' => '#d97706', 'danger' => '#dc2626', 'gray' => '#94a3b8'];
    $scoreColor = $scoreColors[$goalScore['color']] ?? '#94a3b8';
    $tipIcons = ['danger' => '🔴', 'warning' => '🟠', 'info' => '🔵', 'success' => '🟢'];
  @endphp
  <div class="zs-card zs-goals">
    <div class="zs-top">
      <div>
        <div class="zs-h">🎯 Objectifs du mois &amp; note de la stratégie</div>
        <p class="zs-sub" style="margin:0">
          @if($goalElapsed > 0 && $goalElapsed < 1)
            Mois écoulé à {{ (int) round($goalElapsed * 100) }} % — « Projection » = résultat estimé en fin de mois au rythme actuel.
          @elseif($goalElapsed >= 1) Mois terminé : résultats définitifs.
          @else Mois à venir.
          @endif
        </p>
      </div>
      <div class="zs-pills">
        @foreach($goalMonths as $key => $label)
          <button type="button" wire:click="setGoalMonth('{{ $key }}')" class="zs-pill {{ $goalMonth === $key ? 'is-on' : '' }}">{{ $label }}</button>
        @endforeach
      </div>
    </div>

    <div class="zs-goalGrid">
      <div class="zs-scoreBox" style="--c: {{ $scoreColor }}; --p: {{ $goalScore['score'] ?? 0 }}">
        <div class="zs-ring"><span>{{ $goalScore['score'] !== null ? $goalScore['score'] : '—' }}</span><small>/100</small></div>
        <div class="zs-verdict" style="color: {{ $scoreColor }}">{{ $goalScore['verdict'] }}</div>
        <div class="zs-sub" style="margin:0;text-align:center">{{ $goalScore['summary'] }}</div>
        <a class="zs-a" style="font-size:.8rem;font-weight:700;margin-top:.4rem" href="{{ $goalsUrl }}">Gérer les objectifs →</a>
      </div>

      <div>
        @if($goalRows->isEmpty())
          <div class="zs-empty">Aucun objectif pour ce mois. <a class="zs-a" href="{{ $goalsUrl }}">Fixer les objectifs →</a></div>
        @else
          <table class="zs-table">
            <thead><tr><th>Objectif</th><th>Périmètre</th><th class="n">Cible</th><th class="n">Réalisé</th><th class="n">Projection</th><th style="width:22%">Avancement</th><th>Statut</th></tr></thead>
            <tbody>
            @foreach($goalRows as $g)
              @php($bar = min(100, $g->percent))
              <tr>
                <td class="zs-title">{{ $g->label }}</td>
                <td>@if($g->country){!! \App\Support\Markets::flagHtml($g->country) !!}@else 🌍@endif {{ $g->scope }}</td>
                <td class="n">{{ $fmt($g->target) }}{{ $g->unit }}</td>
                <td class="n"><strong>{{ $fmt($g->actual) }}{{ $g->unit }}</strong></td>
                <td class="n">{{ $fmt($g->projected) }}{{ $g->unit }}</td>
                <td><div class="zs-meter" style="margin:0"><span class="zs-meter--{{ $g->color }}" style="width: {{ $bar }}%"></span></div>
                  <span style="font-size:.7rem;opacity:.6">{{ $g->percent }} %</span></td>
                <td><span class="zs-badge zs-badge--{{ $g->color }}">{{ $g->status }}</span></td>
              </tr>
            @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>

    <div class="zs-tips">
      <div class="zs-h" style="font-size:.85rem">💡 Ce qu’il faut faire maintenant</div>
      @foreach($goalTips as $tip)
        <div class="zs-tipRow zs-tipRow--{{ $tip['level'] }}">{{ $tipIcons[$tip['level']] ?? '•' }} {{ $tip['text'] }}</div>
      @endforeach
    </div>

    @if($goalHistory->count() > 1)
      <div class="zs-history">
        <span class="zs-sub" style="margin:0">Historique des notes :</span>
        @foreach($goalHistory as $h)
          <span class="zs-badge zs-badge--{{ $h->color }}">{{ $h->label }} : {{ $h->score ?? '—' }}</span>
        @endforeach
      </div>
    @endif
  </div>

  {{-- ===== Marchés prioritaires ===== --}}
  <div class="zs-card">
    <div class="zs-top">
      <div>
        <div class="zs-h">🌍 Nos 5 marchés prioritaires</div>
        <p class="zs-sub" style="margin:0">Tunisie, France, Arabie saoudite, Qatar et Canada, sur la période choisie, comparés à la période précédente.</p>
      </div>
      <div class="zs-focus">
        <strong>{{ $markets['priority_share'] }} %</strong>
        <span>des visiteurs viennent de nos marchés</span>
      </div>
    </div>
    <div class="zs-markets">
      @foreach($markets['rows'] as $m)
        <div class="zs-market {{ $m->code === 'XX' ? 'is-other' : '' }}">
          <div class="zs-marketHead"><span class="zs-marketFlag">@if($m->code === 'XX'){{ $m->flag }}@else{!! \App\Support\Markets::flagHtml($m->code, 18) !!}@endif</span><strong>{{ $m->name }}</strong></div>
          <div class="zs-marketVal">{{ $fmt($m->visitors) }}
            @if($m->delta !== null)
              <span class="zs-delta {{ $m->delta > 0 ? 'zs-up' : ($m->delta < 0 ? 'zs-down' : 'zs-flat') }}">{{ $m->delta > 0 ? '▲' : ($m->delta < 0 ? '▼' : '=') }} {{ abs($m->delta) }} %</span>
            @endif
          </div>
          <div class="zs-marketSub">visiteurs · {{ $m->share }} % du total</div>
          <div class="zs-meter"><span style="width: {{ min(100, $m->share) }}%"></span></div>
          <dl>
            <div><dt>Google</dt><dd>{{ $fmt($m->google) }}</dd></div>
            <div><dt>Blog</dt><dd>{{ $fmt($m->blog) }}</dd></div>
            <div><dt>Inscrits</dt><dd>{{ $fmt($m->signups) }}</dd></div>
          </dl>
        </div>
      @endforeach
    </div>
    @if($markets['unknown'] > 0)
      <p class="zs-sub" style="margin:.6rem 0 0">{{ $fmt($markets['unknown']) }} visiteur(s) au pays non identifié (le pays est fourni par Cloudflare en production).</p>
    @endif
  </div>

  {{-- ===== Parcours complet : site → plateforme ===== --}}
  <div class="zs-card">
    <div class="zs-top">
      <div>
        <div class="zs-h">🧭 Parcours complet — {{ $platformMonth }}</div>
        <p class="zs-sub" style="margin:0">Du visiteur à l’élève fidèle, par pays : où perd-on les familles ? Les 3 dernières étapes viennent de la plateforme (élèves dont le compte a été créé depuis une demande du site).</p>
      </div>
    </div>
    @php($stageKeys = array_keys(\App\Services\Analytics\Funnel::STAGES))
    @php($fCell = fn ($row, $key) => $row[$key] === null ? '—' : $fmt($row[$key]))
    @php($fRate = fn ($row, $key) => ($row['rates'][$key] ?? null) === null ? '' : '<span class="zs-sub" style="display:block;font-size:.72rem">'.$row['rates'][$key].' %</span>')
    <table class="zs-table">
      <thead><tr><th>Pays</th>@foreach(\App\Services\Analytics\Funnel::STAGES as $stageLabel)<th class="n">{{ $stageLabel }}</th>@endforeach</tr></thead>
      <tbody>
        @foreach($funnel['rows'] as $frow)
          <tr>
            <td>{!! \App\Support\Markets::flagHtml($frow['code'], 14) !!} {{ $frow['name'] }}</td>
            @foreach($stageKeys as $sk)
              <td class="n">{{ $fCell($frow, $sk) }}{!! $fRate($frow, $sk) !!}</td>
            @endforeach
          </tr>
        @endforeach
        <tr style="font-weight:700">
          <td>Tous pays</td>
          @foreach($stageKeys as $sk)
            <td class="n">{{ $fCell($funnel['total'], $sk) }}{!! $fRate($funnel['total'], $sk) !!}</td>
          @endforeach
        </tr>
      </tbody>
    </table>
    @if($funnel['leak'])
      <p class="zs-sub" style="margin:.8rem 0 0">⚠️ Plus grosse perte : <strong>{{ $funnel['leak']['from'] }} → {{ $funnel['leak']['to'] }} ({{ $funnel['leak']['rate'] }} %)</strong>. {{ $funnel['leak']['advice'] }}</p>
    @endif
    @if(! $funnel['platform'])
      <p class="zs-sub" style="margin:.6rem 0 0">Les étapes de la plateforme s’afficheront dès qu’elle répondra.</p>
    @endif
    <p class="zs-sub" style="margin:.4rem 0 0">Le % sous chaque chiffre = part de l’étape précédente. « Élèves fidèles » = présents ce mois et le mois précédent (tous élèves du pays).</p>
  </div>

  {{-- ===== Plateforme d'enseignement ===== --}}
  <div class="zs-card">
    <div class="zs-top">
      <div>
        <div class="zs-h">🎓 Plateforme d’enseignement — {{ $platformMonth }}</div>
        <p class="zs-sub" style="margin:0">Le vrai résultat du marketing : des élèves qui suivent les cours et qui restent. Chiffres agrégés, sans donnée personnelle.</p>
      </div>
      @if($platform && $platform['retention_rate'] !== null)
        <div class="zs-focus"><strong>{{ $platform['retention_rate'] }} %</strong><span>des élèves du mois dernier sont restés</span></div>
      @endif
    </div>

    @if($platform)
      @php($pf = fn ($v, $suffix = '') => $v === null ? '—' : $fmt($v).$suffix)
      @php($platformKpis = [
          ['Élèves actifs', $pf($platform['active_students']), 'présents à au moins une séance'],
          ['Nouveaux élèves', $pf($platform['new_students']), 'comptes créés dans le mois'],
          ['Assiduité', $pf($platform['attendance_rate'], ' %'), 'présences / séances pointées'],
          ['Séances données', $pf($platform['seances_held']), $pf($platform['hours_taught']).' h de cours'],
          ['Note des séances', $platform['feedback_average'] === null ? '—' : number_format($platform['feedback_average'], 1, ',', ' ').' / 5', $pf($platform['feedback_count']).' avis d’élèves'],
          ['Élèves inscrits', $pf($platform['students_total']), 'comptes élèves actifs au total'],
        ])
      <div class="zs-markets">
        @foreach($platformKpis as [$label, $value, $sub])
          <div class="zs-market">
            <div class="zs-marketHead"><strong>{{ $label }}</strong></div>
            <div class="zs-marketVal">{{ $value }}</div>
            <div class="zs-marketSub">{{ $sub }}</div>
          </div>
        @endforeach
      </div>
      <table class="zs-table" style="margin-top:1rem">
        <thead><tr><th>Pays (selon le fuseau horaire)</th><th class="n">Élèves inscrits</th><th class="n">Nouveaux</th><th class="n">Actifs ce mois</th></tr></thead>
        <tbody>
          @foreach(['TN', 'FR', 'SA', 'QA', 'CA', 'other', 'unknown'] as $cc)
            @php($row = $platform['by_country'][$cc] ?? null)
            @continue(! $row)
            <tr>
              @php($countryLabel = \App\Support\Markets::has($cc)
                ? \App\Support\Markets::flagHtml($cc, 14).' '.e(\App\Support\Markets::name($cc, 'fr'))
                : ($cc === 'other' ? '🌐 Autres pays' : '❔ Fuseau non renseigné'))
              <td>{!! $countryLabel !!}</td>
              <td class="n">{{ $fmt($row['students_total']) }}</td>
              <td class="n">{{ $fmt($row['new_students']) }}</td>
              <td class="n"><strong>{{ $fmt($row['active_students']) }}</strong></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @elseif($platformConfigured)
      <p class="zs-sub" style="margin:1rem 0 0">⚠️ La plateforme ne répond pas pour le moment (les chiffres reviendront automatiquement). Vérifiez que <code>MARKETING_API_TOKEN</code> (plateforme) et <code>PLATFORM_STATS_TOKEN</code> (site) sont identiques.</p>
    @else
      <p class="zs-sub" style="margin:1rem 0 0">Pour afficher ici les élèves actifs, la fidélisation et l’assiduité : générez un jeton, mettez-le dans <code>MARKETING_API_TOKEN</code> du <code>.env</code> de la plateforme et dans <code>PLATFORM_STATS_TOKEN</code> du <code>.env</code> du site.</p>
    @endif
  </div>

  {{-- KPIs --}}
  <div class="zs-kpis">
    @foreach($kpiCards as [$key, $label, $hint, $lowerIsBetter])
      @php($k = $kpis[$key])
      <div class="zs-card zs-kpi">
        <div class="zs-label">{{ $label }}</div>
        <div class="zs-val">
          {{ $fmt($k['value']) }}{{ in_array($key, ['bounce', 'conversion']) ? ' %' : '' }}
          @if($k['delta'] !== null)
            @php($good = $lowerIsBetter ? $k['delta'] < 0 : $k['delta'] > 0)
            <span class="zs-delta {{ $k['delta'] == 0 ? 'zs-flat' : ($good ? 'zs-up' : 'zs-down') }}">
              {{ $k['delta'] > 0 ? '▲' : ($k['delta'] < 0 ? '▼' : '=') }} {{ abs($k['delta']) }} %
            </span>
          @endif
        </div>
        <div class="zs-hint">{{ $hint }} Avant : {{ $fmt($k['previous']) }}</div>
      </div>
    @endforeach
  </div>

  {{-- Courbe --}}
  <div class="zs-card">
    <div class="zs-top">
      <div>
        <div class="zs-h">📈 Fréquentation par {{ $timeline['unit'] }}</div>
      </div>
      <div class="zs-legend">
        <span><i style="background:#d97706"></i>Visiteurs</span>
        <span><i style="background:rgba(217,119,6,.25)"></i>Pages vues</span>
      </div>
    </div>
    <div class="zs-chart">
      @foreach($timeline['rows'] as $row)
        <div class="zs-col" title="{{ $row['label'] }} — {{ $row['visitors'] }} visiteurs, {{ $row['pageviews'] }} pages vues">
          <div class="zs-bar-pv" style="height: {{ $row['pageviews'] / $maxTimeline * 100 }}%"></div>
          <div class="zs-bar-v" style="height: {{ $row['visitors'] / $maxTimeline * 100 }}%"></div>
        </div>
      @endforeach
    </div>
    <div class="zs-axis">
      <span>{{ $timeline['rows']->first()['label'] ?? '' }}</span>
      <span>{{ $timeline['rows']->last()['label'] ?? '' }}</span>
    </div>
  </div>

  {{-- Sources & pays --}}
  <div class="zs-grid2">
    <div class="zs-card">
      <div class="zs-h">🧭 D’où viennent les visiteurs</div>
      <p class="zs-sub">Source de la première visite. « Direct » = adresse tapée, favori ou lien non identifié.</p>
      @forelse($sources as $s)
        <div class="zs-row">
          <span>{{ $s->key }}</span><span><strong>{{ $fmt($s->visitors) }}</strong> · {{ $s->percent }} %</span>
          <div class="zs-meter"><span style="width: {{ $s->percent }}%"></span></div>
        </div>
      @empty
        <div class="zs-empty">Pas encore de données.</div>
      @endforelse
      @if($referrers->isNotEmpty())
        <p class="zs-sub" style="margin-top:1rem">Sites qui envoient des visiteurs</p>
        <table class="zs-table">
          @foreach($referrers as $r)
            <tr><td>{{ $r->key }}</td><td class="n">{{ $fmt($r->visitors) }}</td></tr>
          @endforeach
        </table>
      @endif
    </div>

    <div class="zs-card">
      <div class="zs-h">🌍 Pays</div>
      <p class="zs-sub">Visiteurs uniques par pays.</p>
      @forelse($countries as $c)
        <div class="zs-row">
          <span>{{ $flag($c->key) }} {{ $country($c->key) }}</span><span><strong>{{ $fmt($c->visitors) }}</strong> · {{ $c->percent }} %</span>
          <div class="zs-meter"><span style="width: {{ $c->percent }}%"></span></div>
        </div>
      @empty
        <div class="zs-empty">Pas encore de données.</div>
      @endforelse
    </div>
  </div>

  {{-- Pages --}}
  <div class="zs-grid2">
    <div class="zs-card">
      <div class="zs-h">🔥 Pages les plus visitées</div>
      <p class="zs-sub">Toutes les pages, y compris l’espace élève.</p>
      @if($topPages->isEmpty())
        <div class="zs-empty">Pas encore de données.</div>
      @else
        <table class="zs-table">
          <thead><tr><th>Page</th><th class="n">Vues</th><th class="n">Visiteurs</th></tr></thead>
          <tbody>
          @foreach($topPages as $p)
            <tr>
              <td><div class="zs-title">{{ \Illuminate\Support\Str::limit($p->title ?: $p->path, 60) }}</div>
                <a class="zs-path zs-a" href="{{ url($p->path) }}" target="_blank">{{ $p->path }}</a></td>
              <td class="n">{{ $fmt($p->views) }}</td><td class="n">{{ $fmt($p->visitors) }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="zs-card">
      <div class="zs-h">🧊 Pages les moins visitées</div>
      <p class="zs-sub">Pages publiques du site (celles envoyées à Google). À améliorer : titre, liens depuis l’accueil, articles de blog qui pointent vers elles.</p>
      <table class="zs-table">
        <thead><tr><th>Page</th><th class="n">Vues</th></tr></thead>
        <tbody>
        @foreach($leastPages as $p)
          <tr>
            <td><div class="zs-title">{{ \Illuminate\Support\Str::limit($p->title, 60) }}</div>
              <a class="zs-path zs-a" href="{{ url($p->path) }}" target="_blank">{{ $p->path }}</a></td>
            <td class="n {{ $p->views === 0 ? 'zs-zero' : '' }}">{{ $fmt($p->views) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="zs-grid2">
    <div class="zs-card">
      <div class="zs-h">🚪 Pages d’arrivée</div>
      <p class="zs-sub">La première page vue : c’est votre « vitrine » réelle (souvent depuis Google ou Facebook).</p>
      @if($entryPages->isEmpty())
        <div class="zs-empty">Pas encore de données.</div>
      @else
        <table class="zs-table">
          <thead><tr><th>Page</th><th class="n">Arrivées</th></tr></thead>
          <tbody>
          @foreach($entryPages as $p)
            <tr><td><div class="zs-title">{{ \Illuminate\Support\Str::limit($p->title ?: $p->path, 60) }}</div><span class="zs-path">{{ $p->path }}</span></td>
              <td class="n">{{ $fmt($p->entries) }}</td></tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="zs-card">
      <div class="zs-h">📝 Articles du blog</div>
      <p class="zs-sub">
        Lus jusqu’au bout :
        @foreach([25, 50, 75, 100] as $step)
          {{ $step }} % → <strong>{{ $fmt($readDepth['Lecture article '.$step.' %'] ?? 0) }}</strong>@if(! $loop->last) · @endif
        @endforeach
      </p>
      @if($blogPosts->isEmpty())
        <div class="zs-empty">Aucune lecture d’article sur la période. <a class="zs-a" href="{{ \App\Filament\Resources\BlogPosts\BlogPostResource::getUrl('create') }}">Écrire un article →</a></div>
      @else
        <table class="zs-table">
          <thead><tr><th>Article</th><th class="n">Vues</th><th class="n">Lecteurs</th></tr></thead>
          <tbody>
          @foreach($blogPosts as $p)
            <tr><td><div class="zs-title">{{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before($p->title ?: $p->path, ' | '), 60) }}</div><span class="zs-path">{{ $p->path }}</span></td>
              <td class="n">{{ $fmt($p->views) }}</td><td class="n">{{ $fmt($p->visitors) }}</td></tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>

  {{-- Clics --}}
  <div class="zs-grid2">
    <div class="zs-card">
      <div class="zs-h">👆 Éléments les plus cliqués</div>
      <p class="zs-sub">Boutons et liens cliqués par les visiteurs (menu, « Commencer », cartes de cours…).</p>
      @if($topClicks->isEmpty())
        <div class="zs-empty">Pas encore de clics enregistrés.</div>
      @else
        <table class="zs-table">
          <thead><tr><th>Élément</th><th>Type</th><th class="n">Clics</th><th class="n">Pers.</th></tr></thead>
          <tbody>
          @foreach($topClicks as $c)
            <tr>
              <td><div class="zs-title">{{ \Illuminate\Support\Str::limit($c->label, 50) }}</div>
                @if($c->target)<span class="zs-path">→ {{ \Illuminate\Support\Str::limit($c->target, 60) }}</span>@endif</td>
              <td style="font-size:.72rem;opacity:.7">{{ $kindLabels[$c->kind] ?? $c->kind }}</td>
              <td class="n">{{ $fmt($c->clicks) }}</td><td class="n">{{ $fmt($c->visitors) }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="zs-card">
      <div class="zs-h">🖱️ Pages où l’on clique le plus</div>
      @if($clicksByPage->isEmpty())
        <div class="zs-empty">Pas encore de données.</div>
      @else
        <table class="zs-table">
          @foreach($clicksByPage as $c)
            <tr><td><span class="zs-path" style="opacity:.85">{{ $c->page_path }}</span></td><td class="n">{{ $fmt($c->clicks) }}</td></tr>
          @endforeach
        </table>
      @endif

      <div class="zs-h" style="margin-top:1.2rem">↗️ Liens sortants</div>
      <p class="zs-sub">WhatsApp, Facebook, YouTube… : les visiteurs qui vous contactent ou vous suivent.</p>
      @if($outbound->isEmpty())
        <div class="zs-empty">Aucun clic vers un autre site.</div>
      @else
        <table class="zs-table">
          @foreach($outbound as $o)
            <tr><td><div class="zs-title">{{ \Illuminate\Support\Str::limit($o->label, 40) }}</div><span class="zs-path">{{ \Illuminate\Support\Str::limit($o->target, 60) }}</span></td><td class="n">{{ $fmt($o->clicks) }}</td></tr>
          @endforeach
        </table>
      @endif
    </div>
  </div>

  {{-- Technique --}}
  <div class="zs-grid3">
    <div class="zs-card">
      <div class="zs-h">📱 Appareils</div>
      @forelse($devices as $d)
        <div class="zs-row"><span>{{ $deviceLabels[$d->key] ?? $d->key }}</span><span><strong>{{ $d->percent }} %</strong></span>
          <div class="zs-meter"><span style="width: {{ $d->percent }}%"></span></div></div>
      @empty <div class="zs-empty">—</div> @endforelse

      <div class="zs-h" style="margin-top:1rem">🗣️ Langue du site</div>
      @forelse($locales as $l)
        <div class="zs-row"><span>{{ $localeLabels[$l->key] ?? $l->key }}</span><span><strong>{{ $l->percent }} %</strong></span>
          <div class="zs-meter"><span style="width: {{ $l->percent }}%"></span></div></div>
      @empty <div class="zs-empty">—</div> @endforelse
    </div>
    <div class="zs-card">
      <div class="zs-h">🌐 Navigateurs</div>
      @forelse($browsers as $b)
        <div class="zs-row"><span>{{ $b->key }}</span><span><strong>{{ $b->percent }} %</strong></span>
          <div class="zs-meter"><span style="width: {{ $b->percent }}%"></span></div></div>
      @empty <div class="zs-empty">—</div> @endforelse
    </div>
    <div class="zs-card">
      <div class="zs-h">💻 Systèmes</div>
      @forelse($systems as $o)
        <div class="zs-row"><span>{{ $o->key }}</span><span><strong>{{ $o->percent }} %</strong></span>
          <div class="zs-meter"><span style="width: {{ $o->percent }}%"></span></div></div>
      @empty <div class="zs-empty">—</div> @endforelse
    </div>
  </div>

  <div class="zs-grid2">
    <div class="zs-card">
      <div class="zs-h">🕐 Heures d’affluence</div>
      <p class="zs-sub">Pages vues par heure (UTC, Tunisie = +1 h). Idéal pour programmer vos publications Facebook.</p>
      <div class="zs-hours">
        @foreach($hours as $h)
          <div style="height: {{ $h->views / $maxHour * 100 }}%" title="{{ $h->hour }}h — {{ $h->views }} pages vues"></div>
        @endforeach
      </div>
      <div class="zs-axis"><span>0h</span><span>6h</span><span>12h</span><span>18h</span><span>23h</span></div>
    </div>

    <div class="zs-card">
      <div class="zs-h">📣 Campagnes (liens UTM)</div>
      <p class="zs-sub">
        Ajoutez <code>?utm_source=facebook&amp;utm_campaign=rentree2026</code> à vos liens publicitaires pour les suivre ici.
      </p>
      @if($campaigns->isEmpty())
        <div class="zs-empty">Aucune campagne suivie sur la période.</div>
      @else
        <table class="zs-table">
          @foreach($campaigns as $c)
            <tr><td>{{ $c->key }}</td><td class="n">{{ $fmt($c->visitors) }} visiteurs</td></tr>
          @endforeach
        </table>
      @endif
    </div>
  </div>

  {{-- Évolution mensuelle --}}
  <div class="zs-card">
    <div class="zs-h">🗓️ Évolution mois par mois</div>
    <p class="zs-sub">Le tableau de bord du suivi mensuel (12 derniers mois, indépendant du filtre ci-dessus).</p>
    <div style="overflow-x:auto">
      <table class="zs-table">
        <thead>
          <tr><th>Mois</th><th class="n">Visiteurs</th><th class="n">Évol.</th><th class="n">Pages vues</th><th class="n">Clics</th>
            <th class="n">Inscriptions</th><th class="n">Commandes</th><th class="n">Payées</th></tr>
        </thead>
        <tbody>
        @foreach($monthly as $m)
          <tr>
            <td class="zs-title">{{ $m->month }}</td>
            <td class="n"><strong>{{ $fmt($m->visitors) }}</strong></td>
            <td class="n">
              @if($m->growth !== null)
                <span class="zs-delta {{ $m->growth > 0 ? 'zs-up' : ($m->growth < 0 ? 'zs-down' : 'zs-flat') }}">{{ $m->growth > 0 ? '+' : '' }}{{ $m->growth }} %</span>
              @else — @endif
            </td>
            <td class="n">{{ $fmt($m->pageviews) }}</td>
            <td class="n">{{ $fmt($m->clicks) }}</td>
            <td class="n">{{ $fmt($m->signups) }}</td>
            <td class="n">{{ $fmt($m->orders) }}</td>
            <td class="n">{{ $fmt($m->paid) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>
</x-filament-panels::page>
