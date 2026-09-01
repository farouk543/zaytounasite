@extends('layouts.app')

@section('content')
@php
  $levelsArr = ($levels ?? collect())->map(function($l){
    $slug = (string) $l->slug;

    $group = 'primary';
    if (str_starts_with($slug, 'middle') || str_contains($slug, 'college')) $group = 'middle';
    if (str_starts_with($slug, 'lycee') || $slug === 'baccalaureat') $group = 'lycee';
    if (str_starts_with($slug, 'quran')) $group = 'quran';

    return [
      'id' => $l->id,
      'slug' => $l->slug,
      'name' => $l->name_i18n ?? $l->name,
      'group' => $group,
    ];
  })->values();

  $branchesArr = ($branches ?? collect())->map(fn($b) => [
    'id' => $b->id,
    'level_id' => $b->level_id,
    'name' => $b->name_i18n ?? $b->name,
  ])->values();

  $subjectsArr = ($subjects ?? collect())->map(fn($s) => [
    'id' => $s->id,
    'level_id' => $s->level_id,
    'branch_id' => $s->branch_id,
    'name' => $s->name_i18n ?? $s->name,
  ])->values();

  $trackTitle = match($track->slug){
    'tunisia' => __('ui.regimes.tunisia_title'),
    'qatar'   => __('ui.regimes.qatar_title'),
    'saudi'   => __('ui.regimes.saudi_title'),
    'quran'   => __('ui.regimes.quran_title'),
    'france'  => __('ui.regimes.france_title'),
    default   => ($track->name_i18n ?? $track->name ?? __('ui.regimes.default_title')),
  };

  $trackFlag = match($track->slug){
    'tunisia' => '🇹🇳',
    'qatar' => '🇶🇦',
    'saudi' => '🇸🇦',
    'quran' => '📖',
    'france' => '🇫🇷',
    default => '🎓',
  };

  $groupLabels = $groupLabels ?? [
    'primary' => __('ui.regimes.groups.primary'),
    'middle'  => __('ui.regimes.groups.middle'),
    'lycee'   => __('ui.regimes.groups.lycee'),
    'quran'   => __('ui.regimes.groups.quran'),
  ];

  $packs = $packs ?? [
    [
      'key'=>'individual',
      'title'=>__('ui.packs.individual_title'),
      'price'=>__('ui.packs.individual_price'),
      'features'=>[
        __('ui.packs.individual_f1'),
        __('ui.packs.individual_f2'),
        __('ui.packs.individual_f3'),
      ],
      'highlight'=>true
    ],
    [
      'key'=>'duo',
      'title'=>__('ui.packs.duo_title'),
      'price'=>__('ui.packs.duo_price'),
      'features'=>[
        __('ui.packs.duo_f1'),
        __('ui.packs.duo_f2'),
        __('ui.packs.duo_f3'),
      ],
      'highlight'=>false
    ],
    [
      'key'=>'group',
      'title'=>__('ui.packs.group_title'),
      'price'=>__('ui.packs.group_price'),
      'features'=>[
        __('ui.packs.group_f1'),
        __('ui.packs.group_f2'),
        __('ui.packs.group_f3'),
      ],
      'highlight'=>false
    ],
    [
      'key'=>'recorded',
      'title'=>__('ui.packs.recorded_title'),
      'price'=>__('ui.packs.recorded_price'),
      'features'=>[
        __('ui.packs.recorded_f1'),
        __('ui.packs.recorded_f2'),
        __('ui.packs.recorded_f3'),
        __('ui.packs.recorded_f4'),
      ],
      'highlight'=>false
    ],
    [
      'key'=>'custom',
      'title'=>__('ui.packs.custom_title'),
      'price'=>__('ui.packs.custom_price'),
      'features'=>[
        __('ui.packs.custom_f1'),
        __('ui.packs.custom_f2'),
        __('ui.packs.custom_f3'),
      ],
      'highlight'=>true
    ],
  ];
@endphp

<section class="za-hero" aria-label="Regime">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');"></div>
  <div class="za-heroOverlay" aria-hidden="true"></div>

  <div class="za-container za-heroInner">
    <div class="za-heroCard" data-reveal>
      <div class="za-badge">
        <span class="dot"></span>
        <span>{{ __('ui.regimes.hero_badge') }}</span>
      </div>

      <h1 class="za-heroTitle">
        {{ $trackTitle }} <span style="opacity:.9">{{ $trackFlag }}</span>
      </h1>

      <p class="za-heroText">
        {{ __('ui.regimes.hero_text_line1') }}
        <br>{!! __('ui.regimes.hero_text_line2_html') !!}
      </p>

      <div class="za-heroActions" data-reveal="delay-1">
        <a class="btn-primary za-btnLg" href="#select">{{ __('ui.regimes.hero_cta_select') }}</a>
        <a class="btn-outline za-btnLg" href="{{ route('catalog') }}">{{ __('ui.regimes.hero_cta_courses') }}</a>
      </div>

      <div class="za-heroStats">
        <div class="stat" data-reveal="delay-1">
          <div class="statNum">{{ count($packs) }}</div>
          <div class="statLabel">{{ __('ui.regimes.stats_packs') }}</div>
        </div>
        <div class="stat" data-reveal="delay-2">
          <div class="statNum">{{ max(1, ($levels ?? collect())->count()) }}+</div>
          <div class="statLabel">{{ __('ui.regimes.stats_grades') }}</div>
        </div>
        <div class="stat" data-reveal="delay-3">
          <div class="statNum">{{ max(1, ($subjects ?? collect())->count()) }}+</div>
          <div class="statLabel">{{ __('ui.regimes.stats_subjects') }}</div>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="application/json" id="regimeCfg">
{!! json_encode([
  'levels' => $levelsArr,
  'branches' => $branchesArr,
  'subjects' => $subjectsArr,
  'groupLabels' => $groupLabels,
  'startUrl' => route('regimes.france.start'),
  'currencySymbol' => $currencySymbol,
  'modePrices'     => $modePrices,
  'discounts' => [
    'monthly' => 0.00,
    'quarterly' => 0.10,
    'yearly' => 0.20,
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<section
  class="za-section za-sectionAlt za-regimeSelect"
  id="select"
  x-data="regimeSelector(JSON.parse(document.getElementById('regimeCfg').textContent))"
>
  <div class="za-container" style="position:relative; z-index:2;">
    @if(session('error'))
      <div class="lux-card" style="padding:14px; border-left:4px solid #ef4444; margin-top:14px;">
        ❌ {{ session('error') }}
      </div>
    @endif

    <div class="za-sectionHead" data-reveal>
      <div class="pill">{{ __('ui.regimes.select_pill') }}</div>
      <h2 class="za-h2">{{ __('ui.regimes.select_title') }}</h2>
      <p class="za-muted">{{ __('ui.regimes.select_text') }}</p>
    </div>

    <div x-show="toast.open" x-transition.opacity
         class="lux-card"
         style="padding:14px; border-left:4px solid #f59e0b; margin-top:14px;">
      <strong x-text="toast.title"></strong>
      <div class="za-muted" style="margin-top:6px;" x-text="toast.text"></div>
    </div>

    <div class="lux-card" style="padding:22px; margin-top:18px;">
      <div>
        <div style="font-weight:900; font-size:18px;">{{ __('ui.regimes.level') }}</div>
        <div class="za-muted" style="margin-top:6px;">{{ __('ui.regimes.level_hint') }}</div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
          <template x-for="g in groups" :key="g">
            <button type="button"
              @click="pickGroup(g)"
              :class="group === g ? 'btn-primary' : 'btn-outline'"
              class="za-btnLg"
              style="padding:10px 16px; border-radius:999px;"
              x-text="groupLabels[g] ?? g"
            ></button>
          </template>
        </div>
      </div>

      <div style="margin-top:18px;">
        <div style="font-weight:900; font-size:18px;">{{ __('ui.regimes.grade') }}</div>
        <div class="za-muted" style="margin-top:6px;">{{ __('ui.regimes.grade_hint') }}</div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
          <template x-for="l in filteredLevels" :key="l.id">
            <button type="button"
              @click="pickLevel(l.id)"
              :class="levelId === l.id ? 'btn-primary' : 'btn-outline'"
              class="za-btnLg"
              style="padding:10px 16px; border-radius:14px;"
              x-text="l.name"
            ></button>
          </template>

          <div x-show="group && filteredLevels.length === 0" class="za-muted" style="margin-top:8px;">
            {{ __('ui.regimes.no_grades') }}
          </div>

          <div x-show="!group" class="za-muted" style="margin-top:8px;">
            {{ __('ui.regimes.pick_level_first') }}
          </div>
        </div>
      </div>

      <div style="margin-top:18px;" x-show="levelId && levelHasBranches()">
        <div style="font-weight:900; font-size:18px;">{{ __('ui.branch') ?? 'Branche' }}</div>
        <div class="za-muted" style="margin-top:6px;">{{ __('ui.branch_hint') ?? 'Choisissez la branche correspondant à votre niveau.' }}</div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
          <template x-for="b in filteredBranches" :key="b.id">
            <button type="button"
              @click="pickBranch(b.id)"
              :class="branchId === b.id ? 'btn-primary' : 'btn-outline'"
              class="za-btnLg"
              style="padding:10px 16px; border-radius:14px;"
              x-text="b.name"
            ></button>
          </template>

          <div x-show="levelId && filteredBranches.length === 0" class="za-muted" style="margin-top:8px;">
            {{ __('ui.no_branches') ?? 'Aucune branche disponible.' }}
          </div>
        </div>
      </div>

      <div style="margin-top:18px;">
        <div style="font-weight:900; font-size:18px;">{{ __('ui.regimes.subject') }}</div>

        <div class="za-muted" style="margin-top:6px;">
          <span>{{ __('ui.regimes.subject_hint') }}</span>
          <span class="za-muted" style="margin-left:10px;">• {{ __('ui.regimes.current') }}</span>
          <strong x-text="currentLabel()"></strong>
        </div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
          <template x-for="s in filteredSubjects" :key="s.id">
            <button type="button"
              @click="pickSubject(s.id)"
              :class="subjectId === s.id ? 'btn-primary' : 'btn-outline'"
              class="za-btnLg"
              style="padding:10px 16px; border-radius:14px;"
              x-text="s.name"
            ></button>
          </template>

          <div x-show="levelId && filteredSubjects.length === 0" class="za-muted" style="margin-top:8px;">
            {{ __('ui.regimes.no_subjects') }}
          </div>

          <div x-show="!levelId" class="za-muted" style="margin-top:8px;">
            {{ __('ui.regimes.pick_grade_first') }}
          </div>
        </div>
      </div>

      <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <span class="pill" :style="group ? '' : 'opacity:.55'">{{ __('ui.regimes.progress_level') }}</span>
        <span style="opacity:.4;">→</span>
        <span class="pill" :style="levelId ? '' : 'opacity:.55'">{{ __('ui.regimes.progress_grade') }}</span>
        <span style="opacity:.4;">→</span>
        <span class="pill" :style="!levelHasBranches() || branchId ? '' : 'opacity:.55'">{{ __('ui.branch') ?? 'Branche' }}</span>
        <span style="opacity:.4;" x-show="levelHasBranches()">→</span>
        <span class="pill" :style="subjectId ? '' : 'opacity:.55'">{{ __('ui.regimes.progress_subject') }}</span>
      </div>
    </div>

    <div class="za-packsWrap">
      <div class="za-sectionHead" style="margin-top:30px;" data-reveal>
        <div class="pill">{{ __('ui.regimes.packs_pill') }}</div>
        <h2 class="za-h2">{{ __('ui.regimes.packs_title') }}</h2>
        <p class="za-muted">{{ __('ui.regimes.packs_text') }}</p>
      </div>

      <div class="za-tracksGrid" style="margin-top:18px;">
        @foreach($packs as $i => $p)
          @php
            $k = $p['key'];
            $highlight = (bool)($p['highlight'] ?? false);
          @endphp

          <article
            class="za-trackCard za-packCard {{ $highlight ? 'is-highlight' : '' }}"
            style="cursor:pointer; transform:translateZ(0);"
            :aria-disabled="(packIsLocked('{{ $k }}')).toString()"
            x-bind:class="packCardClass('{{ $k }}')"
            @click="choosePack('{{ $k }}')"
            data-reveal="delay-{{ $i+1 }}"
          >
            <div class="za-trackBody">
              <div class="za-packHead {{ $k === 'custom' ? 'is-custom' : '' }}">
                  <h3 class="za-trackName">{{ $p['title'] }}</h3>

                  @if($highlight)
                      <span class="pill za-packBadge">{{ __('ui.packs.recommended_badge') }}</span>
                  @endif
              </div>

              <p class="za-trackText" style="font-weight:900; margin-top:6px;">
                {{ $p['price'] }}
              </p>

              <ul class="za-muted" style="margin-top:12px; display:grid; gap:6px;">
                @foreach(($p['features'] ?? []) as $it)
                  <li>✓ {{ $it }}</li>
                @endforeach
              </ul>

              <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between;">
                <span class="za-trackBtn" style="display:inline-flex; gap:8px; align-items:center;">
                  <span x-text="packKey === '{{ $k }}' ? '{{ __('ui.regimes.selected') }}' : '{{ __('ui.regimes.choose') }}'"></span>
                  <span>→</span>
                </span>

                <span class="za-muted" style="font-size:12px;" x-show="packKey === '{{ $k }}'">✓ OK</span>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      <div class="lux-card" style="padding:22px; margin-top:18px;" x-show="packKey === 'custom'" x-transition.opacity>
        <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start;">
          <div>
            <div class="lux-title">{{ __('ui.regimes.custom_title') }}</div>
            <p class="za-muted" style="margin-top:6px;">
              {{ __('ui.regimes.custom_subtitle') }}
            </p>
          </div>
          <div class="pill">
            {{ __('ui.regimes.custom_monthly_subtotal') }} <strong x-text="formatMoney(subtotalMonthly())"></strong>
          </div>
        </div>

        <div style="margin-top:14px; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
          <button type="button" class="btn-outline za-btnLg" @click="addItem()">
            + {{ __('ui.regimes.custom_add_subject') }}
          </button>

          <button type="button" class="btn-outline za-btnLg" @click="addItemFromCurrent()">
            + {{ __('ui.regimes.custom_add_current') }}
          </button>
        </div>

        <div class="za-muted" style="margin-top:10px;">
          {{ __('ui.regimes.custom_example') }}
        </div>

        <div style="margin-top:14px; display:grid; gap:12px;">
          <template x-for="(it, idx) in items" :key="it.id">
            <div class="lux-card" style="padding:14px;">
              <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                <div style="font-weight:900;">{{ __('ui.regimes.line') }} <span x-text="idx+1"></span></div>
                <button type="button" class="btn-outline" @click="removeItem(idx)">{{ __('ui.remove') }}</button>
              </div>

              <div class="za-formGrid2" style="margin-top:10px;">
                <input class="footer-input"
                  style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                  x-model="it.student_name"
                  placeholder="{{ __('ui.regimes.student_placeholder') }}" />

                <select class="footer-input"
                  style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                  x-model.number="it.level_id"
                  @change="it.branch_id = null; it.subject_id = null"
                >
                  <option value="">{{ __('ui.regimes.grade') }}</option>
                  <template x-for="l in levels" :key="l.id">
                    <option :value="l.id" x-text="l.name"></option>
                  </template>
                </select>

                <select class="footer-input"
                  style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                  x-model.number="it.branch_id"
                  @change="it.subject_id = null"
                  x-show="itemLevelHasBranches(it)"
                >
                  <option value="">{{ __('ui.branch') ?? 'Branche' }}</option>
                  <template x-for="b in branchesFor(it)" :key="it.id + '-b-' + b.id">
                    <option :value="b.id" x-text="b.name"></option>
                  </template>
                </select>

                <select class="footer-input"
                  style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                  x-model.number="it.subject_id"
                >
                  <option value="">{{ __('ui.regimes.subject') }}</option>
                  <template x-for="s in subjectsFor(it)" :key="it.id + '-s-' + s.id">
                    <option :value="s.id" x-text="s.name"></option>
                  </template>
                </select>

                <div style="display:flex; gap:10px;">
                  <select class="footer-input"
                    style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                    x-model="it.mode"
                  >
                    <option value="individual">{{ __('ui.regimes.mode_individual') }}</option>
                    <option value="duo">{{ __('ui.regimes.mode_duo') }}</option>
                    <option value="group">{{ __('ui.regimes.mode_group') }}</option>
                  </select>

                  <select class="footer-input"
                    style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                    x-model.number="it.duration_h">
                    <option :value="1">{{ __('ui.regimes.session_1h') }}</option>
                    <option :value="2">{{ __('ui.regimes.session_2h') }}</option>
                  </select>

                  <input type="number" min="1"
                    class="footer-input"
                    style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                    x-model.number="it.sessions_per_month"
                    placeholder="{{ __('ui.regimes.sessions_per_month') }}" />
                </div>
              </div>

              <div style="margin-top:10px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div class="za-muted">
                  {{ __('ui.regimes.line_monthly') }}
                  <strong x-text="formatMoney(lineMonthly(it))"></strong>
                </div>
                <div class="za-muted">
                  (<span x-text="pricePerHour(it)"></span> <span x-text="currencySymbol"></span> × <span x-text="it.duration_h"></span>h × <span x-text="it.sessions_per_month"></span> {{ __('ui.regimes.sessions') }})
                </div>
              </div>
            </div>
          </template>

          <div class="za-muted" x-show="items.length === 0" style="margin-top:6px;">
            {{ __('ui.regimes.custom_empty') }}
          </div>
        </div>

        <div class="lux-card" style="padding:16px; margin-top:16px;">
          <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
            <div>
              <div style="font-weight:900;">{{ __('ui.regimes.plan_title') }}</div>
              <div class="za-muted" style="margin-top:4px;">{{ __('ui.regimes.plan_hint') }}</div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
              <button type="button" class="btn-outline"
                @click="plan='monthly'"
                :class="plan==='monthly' ? 'btn-primary' : ''">{{ __('ui.packs.plan_monthly') }}</button>

              <button type="button" class="btn-outline"
                @click="plan='quarterly'"
                :class="plan==='quarterly' ? 'btn-primary' : ''">{{ __('ui.packs.plan_quarterly') }}</button>

              <button type="button" class="btn-outline"
                @click="plan='yearly'"
                :class="plan==='yearly' ? 'btn-primary' : ''">{{ __('ui.packs.plan_yearly_short') }}</button>
            </div>
          </div>

          <div style="margin-top:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div class="za-muted">
              {{ __('ui.regimes.subtotal') }} <strong x-text="formatMoney(totalForPlan().sub)"></strong><br>
              {{ __('ui.regimes.discount') }} <strong x-text="'-' + formatMoney(totalForPlan().disc)"></strong>
            </div>

            <div style="font-size:22px; font-weight:900; color: var(--emerald-950);">
              {{ __('ui.regimes.total') }} <span x-text="formatMoney(totalForPlan().total)"></span>
            </div>
          </div>

          <div class="za-muted" style="margin-top:8px;" x-show="plan==='yearly'">
            ✅ {{ __('ui.regimes.yearly_save') }} <strong x-text="formatMoney(totalForPlan().disc)"></strong>.
          </div>
        </div>
      </div>
    </div>

    <div class="za-center" style="margin-top:22px;">
      <button
        type="button"
        class="btn-primary za-btnLg"
        style="min-width:260px;"
        :class="canProceed() ? '' : 'is-disabled'"
        :disabled="!canProceed()"
        @click="proceed()"
      >
        {{ __('ui.regimes.continue_to_payment') }} →
      </button>

      <div class="za-muted" style="margin-top:10px;" x-show="!canProceed()">
        <span x-show="packKey !== 'custom'">{{ __('ui.regimes.need_subject_pack') }}</span>
        <span x-show="packKey === 'custom'">{{ __('ui.regimes.need_custom_lines') }}</span>
      </div>
    </div>

    <div class="za-center mt-10">
      <a class="btn-dark za-btnLg" href="{{ route('home') }}">{{ __('ui.regimes.back_tracks') }}</a>
    </div>
  </div>

  <form x-ref="startForm" method="POST" :action="startUrl" style="display:none;">
    @csrf
    <input type="hidden" name="level_id" :value="levelId">
    <input type="hidden" name="branch_id" :value="branchId">
    <input type="hidden" name="subject_id" :value="subjectId">
    <input type="hidden" name="pack" :value="packKey">
    <input type="hidden" name="grade" :value="grade">

    <input type="hidden" name="plan" x-ref="planInput">
    <input type="hidden" name="items" x-ref="itemsInput">
  </form>
</section>
@endsection

@push('scripts')
<script>
function regimeSelector(cfg){
  return {
    levels: cfg.levels || [],
    branches: cfg.branches || [],
    subjects: cfg.subjects || [],
    groupLabels: cfg.groupLabels || {},
    startUrl: cfg.startUrl,

    group: null,
    levelId: null,
    branchId: null,
    subjectId: null,
    grade: null,
    packKey: null,

    currencySymbol: cfg.currencySymbol || 'DT',
    modePrices: cfg.modePrices || { individual:50, duo:38, group:30 },
    discounts: cfg.discounts ?? { monthly:0, quarterly:0.10, yearly:0.20 },
    plan: 'yearly',
    items: [],

    toast: { open:false, title:'', text:'' },

    formatMoney(v){
      return Number(v || 0).toFixed(0) + ' ' + this.currencySymbol;
    },

    get groups(){
      return Array.from(new Set(this.levels.map(l => l.group)));
    },

    get filteredLevels(){
      if(!this.group) return [];
      return this.levels.filter(l => l.group === this.group);
    },

    get filteredBranches(){
      if(!this.levelId) return [];
      return this.branches.filter(b => String(b.level_id) === String(this.levelId));
    },

    levelHasBranches(){
      return this.filteredBranches.length > 0;
    },

    get filteredSubjects(){
      if(!this.levelId) return [];

      if(this.levelHasBranches()){
        if(!this.branchId) return [];
        return this.subjects.filter(s =>
          String(s.level_id) === String(this.levelId) &&
          String(s.branch_id) === String(this.branchId)
        );
      }

      return this.subjects.filter(s =>
        String(s.level_id) === String(this.levelId) &&
        (s.branch_id === null || s.branch_id === '' || typeof s.branch_id === 'undefined')
      );
    },

    branchesFor(it){
      if (!it?.level_id) return [];
      return this.branches.filter(b => String(b.level_id) === String(it.level_id));
    },

    itemLevelHasBranches(it){
      return this.branchesFor(it).length > 0;
    },

    subjectsFor(it){
      if (!it?.level_id) return [];

      if (this.itemLevelHasBranches(it)) {
        if (!it.branch_id) return [];
        return this.subjects.filter(s =>
          String(s.level_id) === String(it.level_id) &&
          String(s.branch_id) === String(it.branch_id)
        );
      }

      return this.subjects.filter(s =>
        String(s.level_id) === String(it.level_id) &&
        (s.branch_id === null || s.branch_id === '' || typeof s.branch_id === 'undefined')
      );
    },

    pickGroup(g){
      this.group = g;
      this.levelId = null;
      this.branchId = null;
      this.subjectId = null;
      if(this.packKey !== 'custom') this.packKey = null;
      this.hideToast();
    },

    pickLevel(id){
      this.levelId = id;
      this.branchId = null;
      this.subjectId = null;
      if(this.packKey !== 'custom') this.packKey = null;
      this.hideToast();
    },

    pickBranch(id){
      this.branchId = id;
      this.subjectId = null;
      if(this.packKey !== 'custom') this.packKey = null;
      this.hideToast();
    },

    pickSubject(id){
      this.subjectId = id;
      this.hideToast();
    },

    currentLabel(){
      if(!this.group) return '—';
      const lvl = this.levels.find(x => x.id === this.levelId);
      const br = this.branches.find(x => x.id === this.branchId);
      const sub = this.subjects.find(x => x.id === this.subjectId);
      return [this.groupLabels[this.group] || this.group, lvl?.name, br?.name, sub?.name].filter(Boolean).join(' • ') || '—';
    },

    canChoosePack(){
      if (!this.group || !this.levelId || !this.subjectId) return false;
      if (this.levelHasBranches() && !this.branchId) return false;
      return true;
    },

    packIsLocked(key){
      if(key === 'custom') return false;
      return !this.canChoosePack();
    },

    packCardClass(key){
      const baseDisabled = this.packIsLocked(key);
      const isSelected = this.packKey === key;
      return [
        baseDisabled ? 'is-disabled' : 'is-ready',
        isSelected ? 'is-selected' : '',
      ].join(' ');
    },

    showToast(title, text){
      this.toast = { open:true, title, text };
      window.clearTimeout(this._toastTimer);
      this._toastTimer = window.setTimeout(() => this.toast.open = false, 4500);
    },

    hideToast(){
      this.toast.open = false;
    },

    pricePerHour(it){
      const mode = it?.mode || 'group';
      return Number(this.modePrices?.[mode] ?? 30);
    },

    newItem(payload = {}){
      return Object.assign({
        id: (window.crypto?.randomUUID?.() || (Date.now() + '-' + Math.random().toString(16).slice(2))),
        student_name: '',
        level_id: null,
        branch_id: null,
        subject_id: null,
        mode: 'group',
        duration_h: 1,
        sessions_per_month: 4,
      }, payload);
    },

    addItem(){
      this.items.push(this.newItem());
    },

    addItemFromCurrent(){
      this.items.push(this.newItem({
        level_id: this.levelId ?? null,
        branch_id: this.branchId ?? null,
        subject_id: this.subjectId ?? null,
      }));
    },

    removeItem(i){
      this.items.splice(i,1);
    },

    lineMonthly(it){
      const h = Number(it.duration_h || 0);
      const spm = Number(it.sessions_per_month || 0);
      const p = this.pricePerHour(it);
      if(!h || !spm) return 0;
      return p * h * spm;
    },

    subtotalMonthly(){
      return this.items.reduce((sum, it) => sum + this.lineMonthly(it), 0);
    },

    monthsForPlan(){
      if(this.plan === 'yearly') return 12;
      if(this.plan === 'quarterly') return 3;
      return 1;
    },

    discountForPlan(){
      return Number(this.discounts?.[this.plan] ?? 0);
    },

    totalForPlan(){
      const months = this.monthsForPlan();
      const sub = this.subtotalMonthly() * months;
      const disc = sub * this.discountForPlan();
      return { sub, disc, total: (sub - disc) };
    },

    choosePack(key){
      if(key === 'custom'){
        this.packKey = 'custom';
        if(this.items.length === 0) this.addItemFromCurrent();
        return;
      }

      if(!this.canChoosePack()){
        this.showToast(
          '{{ __('ui.regimes.toast_incomplete_title') }}',
          '{{ __('ui.regimes.toast_incomplete_text') }}'
        );
        return;
      }

      this.packKey = key;
    },

    customLinesComplete(){
      if(!this.items.length) return false;

      return this.items.every(it => {
        if (!(it.level_id && it.subject_id)) return false;
        if (this.itemLevelHasBranches(it) && !it.branch_id) return false;

        return ['individual','duo','group'].includes(String(it.mode)) &&
          (Number(it.duration_h) === 1 || Number(it.duration_h) === 2) &&
          Number(it.sessions_per_month) >= 1;
      });
    },

    canProceed(){
      if(this.packKey === 'custom'){
        return this.customLinesComplete();
      }
      return this.canChoosePack() && !!this.packKey;
    },

    proceed(){
      if(!this.canProceed()){
        this.showToast(
          '{{ __('ui.regimes.toast_missing_title') }}',
          '{{ __('ui.regimes.toast_missing_text') }}'
        );
        return;
      }

      if(this.packKey === 'custom'){
        this.$refs.planInput.value = this.plan;
        this.$refs.itemsInput.value = JSON.stringify(this.items);
      } else {
        this.$refs.planInput.value = '';
        this.$refs.itemsInput.value = '';
      }

      this.$refs.startForm.submit();
    }
  }
}
</script>

<style>
.za-packCard{
  transition: transform .25s ease, box-shadow .25s ease, opacity .25s ease, filter .25s ease;
  will-change: transform;
}
.za-packCard.is-ready:hover{
  transform: translateY(-6px);
  box-shadow: 0 20px 50px rgba(0,0,0,.12);
}
.za-packCard.is-selected{
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 22px 55px rgba(0,0,0,.14);
  outline: 2px solid rgba(16, 185, 129, .35);
}
.za-packCard.is-disabled{
  opacity: .55;
  filter: grayscale(.15);
  cursor: not-allowed !important;
}

button.is-disabled,
.btn-primary.is-disabled{
  opacity: .55;
  cursor: not-allowed;
}
</style>
@endpush