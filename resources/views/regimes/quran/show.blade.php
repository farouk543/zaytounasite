@extends('layouts.app')

@section('content')
@php
  use App\Services\CurrencyService;

  $levelsArr = collect([
    [
      'id' => 1,
      'slug' => 'niveau-1-initiation-lecture',
      'name' => 'Niveau 1 : Initiation et Lecture',
      'description' => "Apprentissage de la lecture de l'arabe, dechiffrage du texte coranique, et maitrise de la prononciation correcte (lettres et voyelles).",
      'group' => 'quran',
    ],
    [
      'id' => 2,
      'slug' => 'niveau-2-tajwid-fondamental',
      'name' => 'Niveau 2 : Tajwid fondamental et memorisation initiale',
      'description' => "Etude des regles de base du Tajwid (allongements, noon/meem) et memorisation des sourates courtes du 30eme chapitre.",
      'group' => 'quran',
    ],
    [
      'id' => 3,
      'slug' => 'niveau-3-memorisation-intermediaire',
      'name' => 'Niveau 3 : Memorisation intermediaire',
      'description' => "Memorisation de parties plus longues, consolidation de la fluidite, et introduction aux regles de tajwid avancees.",
      'group' => 'quran',
    ],
    [
      'id' => 4,
      'slug' => 'niveau-4-memorisation-avancee',
      'name' => 'Niveau 4 : Memorisation avancee et Tajwid approfondi',
      'description' => "Augmentation du rythme de memorisation, etude du Tafsir pour comprendre les versets, et maitrise avancee du Tajwid.",
      'group' => 'quran',
    ],
    [
      'id' => 5,
      'slug' => 'niveau-5-hifdh-complet',
      'name' => 'Niveau 5 : Hifdh complet',
      'description' => "Finalisation du Coran, revision intensive (Muraajaa), stabilisation de la memorisation et recitation devant un professeur.",
      'group' => 'quran',
    ],
  ])->values();

  $packs = $packs ?? [];

  $visitorCurrency = $visitorCurrency ?? session('app_currency', 'TND');
  $regimeCurrency  = 'TND';
@endphp

<section class="za-hero" aria-label="Quran">
  <div class="za-heroMedia" aria-hidden="true"
       style="background-image:url('{{ asset('assets/hero/hero-campus2.webp') }}');
              filter: saturate(.8) brightness(.6);"></div>
  <div class="za-heroOverlay" aria-hidden="true"
       style="background: linear-gradient(160deg, rgba(10,35,20,.88) 0%, rgba(5,20,10,.75) 100%);"></div>

  <div class="za-container za-heroInner">
    <div class="za-heroCard" data-reveal>
      <div class="za-badge">
        <span class="dot"></span>
        <span>Apprentissage du Quran</span>
      </div>

      <h1 class="za-heroTitle" style="direction:ltr;">
        &#x1F4D6; Quran &amp; Sciences islamiques
      </h1>

      <p class="za-heroText">
        Apprenez le Quran avec des enseignants qualifies, a votre rythme et selon votre niveau.
        <br>Tajweed, memorisation, tafsir et lecture : un parcours complet pour tous.
      </p>

      <div class="za-heroActions" data-reveal="delay-1">
        <a class="btn-primary za-btnLg" href="#select">Choisir mon pack</a>
        <a class="btn-outline za-btnLg" href="{{ route('catalog', ['track' => 'quran']) }}">Voir les cours</a>
      </div>

      <div class="za-heroStats">
        <div class="stat" data-reveal="delay-1">
          <div class="statNum">{{ count($packs) }}</div>
          <div class="statLabel">Formules</div>
        </div>
        <div class="stat" data-reveal="delay-2">
          <div class="statNum">{{ $levelsArr->count() }}</div>
          <div class="statLabel">Niveaux</div>
        </div>
        <div class="stat" data-reveal="delay-3">
          <div class="statNum">100%</div>
          <div class="statLabel">Parcours personalise</div>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="application/json" id="regimeCfg">
{!! json_encode([
  'levels'         => $levelsArr,
  'branches'       => [],
  'groupLabels'    => ['quran' => 'Niveau'],
  'startUrl'       => route('regimes.quran.start'),
  'currencySymbol' => $currencySymbol,
  'modePrices'     => $modePrices,
  'discounts' => [
    'monthly'   => 0.00,
    'quarterly' => 0.10,
    'yearly'    => 0.20,
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<section
  class="za-section za-sectionAlt za-regimeSelect"
  id="select"
  x-data="quranSelector(JSON.parse(document.getElementById('regimeCfg').textContent))"
>
  <div class="za-container" style="position:relative; z-index:2;">
    @if(session('error'))
      <div class="lux-card" style="padding:14px; border-left:4px solid #ef4444; margin-top:14px;">
        ❌ {{ session('error') }}
      </div>
    @endif

    <div class="za-sectionHead" data-reveal>
      <div class="pill">Votre parcours</div>
      <h2 class="za-h2">Choisissez votre niveau et votre formule</h2>
      <p class="za-muted">Selectionnez le niveau qui correspond a votre avancement, puis choisissez la formule la plus adaptee.</p>
    </div>

    <div x-show="toast.open" x-transition.opacity
         class="lux-card" style="padding:14px; border-left:4px solid #f59e0b; margin-top:14px;">
      <strong x-text="toast.title"></strong>
      <div class="za-muted" style="margin-top:6px;" x-text="toast.text"></div>
    </div>

    <div class="lux-card" style="padding:22px; margin-top:18px;">

      <div>
        <div style="font-weight:900; font-size:18px;">Niveau d'apprentissage</div>
        <div class="za-muted" style="margin-top:6px;">Choisissez votre niveau actuel dans l'apprentissage du Quran.</div>

        <div style="margin-top:14px; display:grid; gap:12px;">
          <template x-for="l in levels" :key="l.id">
            <button
              type="button"
              @click="pickLevel(l.id)"
              :class="levelId === l.id ? 'quran-level-card is-selected' : 'quran-level-card'"
            >
              <div style="font-weight:900; color:#0A3A2A; font-size:17px;" x-text="l.name"></div>
              <div style="margin-top:6px; color:#64748b; line-height:1.6; font-size:14px;" x-text="l.description"></div>
            </button>
          </template>
        </div>
      </div>

      <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <span class="pill" :style="levelId ? '' : 'opacity:.55'">Niveau</span>
        <span style="opacity:.4;">→</span>
        <span class="pill" :style="packKey ? '' : 'opacity:.55'">Formule</span>
      </div>
    </div>

    <div class="za-packsWrap">
      <div class="za-sectionHead" style="margin-top:30px;" data-reveal>
        <div class="pill">Nos formules</div>
        <h2 class="za-h2">Choisissez votre formule d'apprentissage</h2>
        <p class="za-muted">Toutes les formules incluent un suivi personnalise par un enseignant qualifie.</p>
      </div>

      <div class="za-tracksGrid" style="margin-top:18px;">
        @foreach($packs as $i => $p)
          @php $k = $p['key']; $highlight = (bool)($p['highlight'] ?? false); @endphp

          <article
            class="za-trackCard za-packCard {{ $highlight ? 'is-highlight' : '' }}"
            style="cursor:pointer;"
            :aria-disabled="(packIsLocked('{{ $k }}')).toString()"
            x-bind:class="packCardClass('{{ $k }}')"
            @click="choosePack('{{ $k }}')"
            data-reveal="delay-{{ $i+1 }}"
          >
            <div class="za-trackBody">
              <div class="za-packHead {{ $k === 'custom' ? 'is-custom' : '' }}">
                <h3 class="za-trackName">{{ $p['title'] }}</h3>
                @if($highlight)
                  <span class="pill za-packBadge">Recommande</span>
                @endif
              </div>

              <p class="za-trackText" style="font-weight:900; margin-top:6px;">{{ $p['price'] }}</p>

              <ul class="za-muted" style="margin-top:12px; display:grid; gap:6px;">
                @foreach(($p['features'] ?? []) as $it)
                  <li>✓ {{ $it }}</li>
                @endforeach
              </ul>

              <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between;">
                <span class="za-trackBtn" style="display:inline-flex; gap:8px; align-items:center;">
                  <span x-text="packKey === '{{ $k }}' ? 'Selectionne' : 'Choisir'"></span>
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
            <div class="lux-title">Pack Personnalise</div>
            <p class="za-muted" style="margin-top:6px;">Composez votre programme sur mesure : plusieurs apprenants ou plusieurs niveaux dans une seule formule.</p>
          </div>
          <div class="pill">Sous-total mensuel : <strong x-text="formatMoney(subtotalMonthly())"></strong></div>
        </div>

        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
          <button type="button" class="btn-outline za-btnLg" @click="addItem()">+ Ajouter un element</button>
          <button type="button" class="btn-outline za-btnLg" @click="addItemFromCurrent()">+ Ajouter le niveau actuel</button>
        </div>

        <div style="margin-top:14px; display:grid; gap:12px;">
          <template x-for="(it, idx) in items" :key="it.id">
            <div class="lux-card" style="padding:14px;">
              <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                <div style="font-weight:900;">Element <span x-text="idx+1"></span></div>
                <button type="button" class="btn-outline" @click="removeItem(idx)">Supprimer</button>
              </div>

              <div class="za-formGrid2" style="margin-top:10px;">
                <input class="footer-input" style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                  x-model="it.student_name" placeholder="Nom de l'apprenant" />

                <select class="footer-input" style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                  x-model.number="it.level_id">
                  <option value="">Niveau</option>
                  <template x-for="l in levels" :key="l.id">
                    <option :value="l.id" x-text="l.name"></option>
                  </template>
                </select>

                <div style="display:flex; gap:10px;">
                  <select class="footer-input" style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)" x-model="it.mode">
                    <option value="individual">Individuel</option>
                    <option value="duo">Duo</option>
                    <option value="group">Groupe</option>
                  </select>
                  <select class="footer-input" style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)" x-model.number="it.duration_h">
                    <option :value="1">1h</option>
                    <option :value="2">2h</option>
                  </select>
                  <input type="number" min="1" class="footer-input" style="background:#fff;color:#0A1410;border-color:rgba(2,44,32,.12)"
                    x-model.number="it.sessions_per_month" placeholder="Seances/mois" />
                </div>
              </div>

              <div style="margin-top:10px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div class="za-muted">Mensuel : <strong x-text="formatMoney(lineMonthly(it))"></strong></div>
                <div class="za-muted">
                  (<span x-text="pricePerHour(it)"></span> <span x-text="currencySymbol"></span> × <span x-text="it.duration_h"></span>h × <span x-text="it.sessions_per_month"></span> séances)
                </div>
              </div>
            </div>
          </template>
          <div class="za-muted" x-show="items.length === 0" style="margin-top:6px;">Ajoutez au moins un element.</div>
        </div>

        <div class="lux-card" style="padding:16px; margin-top:16px;">
          <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
            <div>
              <div style="font-weight:900;">Duree de l'engagement</div>
              <div class="za-muted" style="margin-top:4px;">L'annuel offre -20% sur le total.</div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
              <button type="button" class="btn-outline" @click="plan='monthly'" :class="plan==='monthly' ? 'btn-primary' : ''">Mensuel</button>
              <button type="button" class="btn-outline" @click="plan='quarterly'" :class="plan==='quarterly' ? 'btn-primary' : ''">Trimestriel</button>
              <button type="button" class="btn-outline" @click="plan='yearly'" :class="plan==='yearly' ? 'btn-primary' : ''">Annuel</button>
            </div>
          </div>
          <div style="margin-top:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div class="za-muted">
              Sous-total <strong x-text="formatMoney(totalForPlan().sub)"></strong><br>
              Remise <strong x-text="'-' + formatMoney(totalForPlan().disc)"></strong>
            </div>
            <div style="font-size:22px; font-weight:900; color:var(--emerald-950);">
              Total <span x-text="formatMoney(totalForPlan().total)"></span>
            </div>
          </div>
          <div class="za-muted" style="margin-top:8px;" x-show="plan==='yearly'">
            ✅ Vous economisez <strong x-text="formatMoney(totalForPlan().disc)"></strong> avec l'annuel.
          </div>
        </div>
      </div>
    </div>

    <div class="za-center" style="margin-top:22px;">
      <button type="button" class="btn-primary za-btnLg" style="min-width:260px;"
        :class="canProceed() ? '' : 'is-disabled'" :disabled="!canProceed()" @click="proceed()">
        Continuer vers la validation →
      </button>
      <div class="za-muted" style="margin-top:10px;" x-show="!canProceed()">
        <span x-show="packKey !== 'custom'">Choisissez un niveau et une formule.</span>
        <span x-show="packKey === 'custom'">Completez tous les elements du pack personnalise.</span>
      </div>
    </div>

    <div class="za-center mt-10">
      <a class="btn-dark za-btnLg" href="{{ route('home') }}">Retour a l'accueil</a>
    </div>
  </div>

  <form x-ref="startForm" method="POST" :action="startUrl" style="display:none;">
    @csrf
    <input type="hidden" name="level_id"   :value="levelId">
    <input type="hidden" name="branch_id"  :value="null">
    <input type="hidden" name="subject_id" value="">
    <input type="hidden" name="pack"       :value="packKey">
    <input type="hidden" name="grade"      value="">
    <input type="hidden" name="plan"  x-ref="planInput">
    <input type="hidden" name="items" x-ref="itemsInput">
  </form>
</section>
@endsection

@push('scripts')
<script>
function quranSelector(cfg){
  return {
    levels: cfg.levels || [],
    branches: [],
    startUrl: cfg.startUrl,
    currencySymbol: cfg.currencySymbol || 'DT',

    levelId: null,
    branchId: null,
    subjectId: null,
    grade: null,
    packKey: null,

    modePrices: cfg.modePrices || { individual:50, duo:38, group:30 },
    discounts: cfg.discounts ?? { monthly:0, quarterly:0.10, yearly:0.20 },
    plan: 'yearly',
    items: [],

    toast: { open:false, title:'', text:'' },

    formatMoney(v){
      return Number(v || 0).toFixed(0) + ' ' + this.currencySymbol;
    },

    levelHasBranches(){ return false; },
    itemLevelHasBranches(){ return false; },

    pickLevel(id){
      this.levelId = id;
      if(this.packKey !== 'custom') this.packKey = null;
      this.hideToast();
    },

    currentLabel(){
      const lvl = this.levels.find(x => x.id === this.levelId);
      return lvl?.name || '—';
    },

    canChoosePack(){ return !!this.levelId; },
    packIsLocked(key){ return key === 'custom' ? false : !this.canChoosePack(); },
    packCardClass(key){
      return [
        this.packIsLocked(key) ? 'is-disabled' : 'is-ready',
        this.packKey === key ? 'is-selected' : ''
      ].join(' ');
    },

    showToast(title, text){
      this.toast = { open:true, title, text };
      window.clearTimeout(this._t);
      this._t = setTimeout(() => this.toast.open = false, 4500);
    },

    hideToast(){ this.toast.open = false; },

    pricePerHour(it){
      return Number(this.modePrices?.[it?.mode || 'group'] ?? 30);
    },

    newItem(payload={}){
      return Object.assign({
        id: (crypto?.randomUUID?.() || Date.now()+'-'+Math.random()),
        student_name:'',
        level_id:null,
        branch_id:null,
        subject_id:null,
        mode:'group',
        duration_h:1,
        sessions_per_month:4
      }, payload);
    },

    addItem(){ this.items.push(this.newItem()); },
    addItemFromCurrent(){ this.items.push(this.newItem({ level_id: this.levelId })); },
    removeItem(i){ this.items.splice(i,1); },

    lineMonthly(it){
      const h = Number(it.duration_h || 0);
      const s = Number(it.sessions_per_month || 0);
      const p = this.pricePerHour(it);
      return (h && s) ? p * h * s : 0;
    },

    subtotalMonthly(){
      return this.items.reduce((sum,it) => sum + this.lineMonthly(it), 0);
    },

    monthsForPlan(){
      return this.plan === 'yearly' ? 12 : this.plan === 'quarterly' ? 3 : 1;
    },

    discountForPlan(){
      return Number(this.discounts?.[this.plan] ?? 0);
    },

    totalForPlan(){
      const m = this.monthsForPlan();
      const sub = this.subtotalMonthly() * m;
      const disc = sub * this.discountForPlan();
      return { sub, disc, total: sub - disc };
    },

    choosePack(key){
      if(key === 'custom'){
        this.packKey = 'custom';
        if(!this.items.length) this.addItemFromCurrent();
        return;
      }
      if(!this.canChoosePack()){
        this.showToast('Selection incomplete', 'Choisissez un niveau d\'abord.');
        return;
      }
      this.packKey = key;
    },

    customLinesComplete(){
      if(!this.items.length) return false;
      return this.items.every(it =>
        it.level_id &&
        ['individual','duo','group'].includes(String(it.mode)) &&
        [1,2].includes(Number(it.duration_h)) &&
        Number(it.sessions_per_month) >= 1
      );
    },

    canProceed(){
      return this.packKey === 'custom'
        ? this.customLinesComplete()
        : (this.canChoosePack() && !!this.packKey);
    },

    proceed(){
      if(!this.canProceed()){
        this.showToast('Selection incomplete', 'Completez votre selection avant de continuer.');
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
.za-packCard{ transition: transform .25s ease, box-shadow .25s ease, opacity .25s ease; }
.za-packCard.is-ready:hover{ transform: translateY(-6px); box-shadow: 0 20px 50px rgba(0,0,0,.12); }
.za-packCard.is-selected{ outline: 3px solid var(--emerald-600,#059669); outline-offset:2px; }
.za-packCard.is-disabled{ opacity:.45; filter:grayscale(.4); pointer-events:none; }
.za-packCard.is-highlight{ border-color:rgba(234,179,8,.4); background:linear-gradient(135deg,rgba(234,179,8,.04),rgba(16,185,129,.04)); }
.za-packHead{ display:flex; align-items:flex-start; justify-content:space-between; gap:8px; flex-wrap:wrap; }
.za-packHead.is-custom .za-trackName::before{ content:'✦ '; color:var(--emerald-600,#059669); }
.za-packBadge{ flex-shrink:0; font-size:.72rem; }

.quran-level-card{
  text-align:left;
  padding:16px 18px;
  border-radius:18px;
  border:1px solid rgba(2,44,32,.12);
  background:#fff;
  transition:.25s ease;
  box-shadow:0 10px 30px rgba(0,0,0,.04);
}
.quran-level-card.is-selected{
  border:2px solid var(--emerald-600,#059669) !important;
  box-shadow:0 16px 40px rgba(5,150,105,.12) !important;
  background:linear-gradient(180deg, rgba(16,185,129,.05), #fff) !important;
}
</style>
@endpush