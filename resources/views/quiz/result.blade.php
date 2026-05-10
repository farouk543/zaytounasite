@extends('layouts.app')

@section('content')
<div class="qr-page">
    <div class="qr-bg"></div>

    {{-- Confetti canvas (shown if passed) --}}
    @if($attempt->is_passed)
        <canvas id="confetti-canvas" style="position:fixed;inset:0;pointer-events:none;z-index:9999;"></canvas>
    @endif

    <x-container class="relative z-10 py-10">
        <div class="qr-shell">

            {{-- ── Hero ── --}}
            <div class="qr-hero">
                <div>
                    <div class="qr-badge {{ $attempt->is_passed ? 'qr-badge--pass' : 'qr-badge--fail' }}">
                        {{ $attempt->is_passed ? __('ui.quiz.passed_badge') : __('ui.quiz.failed_badge') }}
                    </div>
                    <h1 class="qr-title">{{ $quiz->title_display }}</h1>
                    <p class="qr-subtitle">
                        Tentative #{{ $attempt->attempt_number }} ·
                        {{ $attempt->submitted_at?->format('d/m/Y à H:i') }}
                    </p>
                </div>

                <div class="qr-scoreCard">
                    <div class="qr-scoreCircle" style="--pct:{{ number_format($attempt->percentage, 0) }}">
                        <svg class="qr-scoreRing" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="10"/>
                            <circle
                                cx="50" cy="50" r="42"
                                fill="none"
                                stroke="{{ $attempt->is_passed ? 'url(#scoreGradPass)' : 'url(#scoreGradFail)' }}"
                                stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="{{ round(2 * M_PI * 42, 2) }}"
                                stroke-dashoffset="{{ round(2 * M_PI * 42 * (1 - $attempt->percentage / 100), 2) }}"
                                transform="rotate(-90 50 50)"
                                style="transition:stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1)"
                            />
                            <defs>
                                <linearGradient id="scoreGradPass" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#6366f1"/>
                                    <stop offset="100%" stop-color="#10b981"/>
                                </linearGradient>
                                <linearGradient id="scoreGradFail" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#f43f5e"/>
                                    <stop offset="100%" stop-color="#eab308"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <span class="qr-pct">{{ number_format($attempt->percentage, 0) }}%</span>
                    </div>

                    <div class="qr-scoreInfo">
                        <strong class="{{ $attempt->is_passed ? 'qr-pass' : 'qr-fail' }}">
                            {{ $attempt->is_passed ? __('ui.quiz.status_passed') : __('ui.quiz.status_failed') }}
                        </strong>
                        <p>{{ __('ui.quiz.points_label', ['score' => $attempt->score, 'max' => $attempt->max_score]) }}</p>
                        <div class="qr-statsRow">
                            @php
                                $correct = $attempt->answers->where('is_correct', true)->count();
                                $total   = $attempt->answers->count();
                                $wrong   = $total - $correct;
                            @endphp
                            <span class="qr-stat qr-statGood">{{ __('ui.quiz.correct_count', ['count' => $correct]) }}</span>
                            <span class="qr-stat qr-statBad">{{ __('ui.quiz.wrong_count', ['count' => $wrong]) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Review ── --}}
            <h2 class="qr-sectionTitle">{{ __('ui.quiz.detailed_review') }}</h2>

            <div class="qr-list">
                @foreach($attempt->answers as $i => $answer)
                    @php
                        $question   = $answer->question;
                        $type       = $question->question_type;
                        $opts       = $question->options;
                        $correctOpts = $opts->where('is_correct', true);
                    @endphp

                    <article class="qr-item {{ $answer->is_correct ? 'is-correct' : 'is-wrong' }}">
                        <div class="qr-itemHead">
                            <span class="qr-qNum">{{ __('ui.quiz.q_prefix') }}{{ $i + 1 }}</span>
                            <h3 class="qr-qText">{{ $question->question_text_display }}</h3>
                            <span class="qr-state {{ $answer->is_correct ? 'qr-stateGood' : 'qr-stateBad' }}">
                                {{ $answer->is_correct ? __('ui.quiz.correct_label') : __('ui.quiz.wrong_label') }}
                            </span>
                        </div>

                        {{-- Question image --}}
                        @if($question->image_path)
                            <div class="qr-qImg">
                                <img src="{{ Storage::disk('public')->url($question->image_path) }}" alt="Illustration" loading="lazy">
                            </div>
                        @endif

                        <div class="qr-itemBody">

                            {{-- Single / true_false --}}
                            @if(in_array($type, ['single_choice', 'true_false', 'multiple_choice']))
                                <div class="qr-optList">
                                    @foreach($opts as $opt)
                                        @php
                                            $studentSelected = false;
                                            if (in_array($type, ['single_choice', 'true_false'])) {
                                                $studentSelected = $answer->selected_option_id === $opt->id;
                                            } else {
                                                // multiple_choice: answer_text is JSON array of IDs
                                                $selectedIds = json_decode($answer->answer_text ?? '[]', true) ?: [];
                                                $studentSelected = in_array($opt->id, $selectedIds);
                                            }
                                        @endphp
                                        <div class="qr-opt
                                            {{ $opt->is_correct ? 'qr-optCorrect' : '' }}
                                            {{ $studentSelected && !$opt->is_correct ? 'qr-optWrong' : '' }}
                                            {{ $studentSelected ? 'qr-optSelected' : '' }}
                                        ">
                                            <span class="qr-optIcon">
                                                @if($opt->is_correct) ✓
                                                @elseif($studentSelected) ✗
                                                @else ○
                                                @endif
                                            </span>
                                            {{ $opt->option_text_display }}
                                            @if($studentSelected && !$opt->is_correct)
                                                <span class="qr-optTag">Ta réponse</span>
                                            @endif
                                            @if($opt->is_correct && !$studentSelected)
                                                <span class="qr-optTag qr-optTagGood">Bonne réponse</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                            {{-- Matching --}}
                            @elseif($type === 'matching')
                                @php
                                    $studentPairs = json_decode($answer->answer_text ?? '{}', true) ?: [];
                                @endphp
                                <table class="qr-matchTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('ui.quiz.match_left_col') }}</th>
                                            <th>{{ __('ui.quiz.match_your_col') }}</th>
                                            <th>{{ __('ui.quiz.match_correct_col') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($opts as $opt)
                                            @php
                                                $studentAnswer = $studentPairs[$opt->id] ?? null;
                                                $isPairCorrect = $studentAnswer === $opt->match_text_display;
                                            @endphp
                                            <tr class="{{ $isPairCorrect ? 'qr-trGood' : 'qr-trBad' }}">
                                                <td>{{ $opt->option_text_display }}</td>
                                                <td>{{ $studentAnswer ?: '—' }}</td>
                                                <td>{{ $opt->match_text_display }}</td>
                                                <td>{{ $isPairCorrect ? '✓' : '✗' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            {{-- Ordering --}}
                            @elseif($type === 'ordering')
                                @php
                                    $studentOrder = json_decode($answer->answer_text ?? '[]', true) ?: [];
                                    $correctOrder = $opts->sortBy('sort_order')->pluck('id')->toArray();
                                    $optById = $opts->keyBy('id');
                                @endphp
                                <div class="qr-orderComparison">
                                    <div class="qr-orderCol">
                                        <div class="qr-orderColHead">{{ __('ui.quiz.order_yours') }}</div>
                                        @foreach($studentOrder as $pos => $optId)
                                            @php $opt = $optById[$optId] ?? null; @endphp
                                            @if($opt)
                                                <div class="qr-orderRow {{ $correctOrder[$pos] == $optId ? 'qr-trGood' : 'qr-trBad' }}">
                                                    <span>{{ $pos + 1 }}.</span>
                                                    <span>{{ $opt->option_text_display }}</span>
                                                    <span>{{ $correctOrder[$pos] == $optId ? '✓' : '✗' }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="qr-orderCol">
                                        <div class="qr-orderColHead">{{ __('ui.quiz.order_correct') }}</div>
                                        @foreach($correctOrder as $pos => $optId)
                                            @php $opt = $optById[$optId] ?? null; @endphp
                                            @if($opt)
                                                <div class="qr-orderRow qr-trGood">
                                                    <span>{{ $pos + 1 }}.</span>
                                                    <span>{{ $opt->option_text_display }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                            {{-- Fill blank / short answer --}}
                            @else
                                <div class="qr-textAnswer">
                                    <div class="qr-textRow">
                                        <span class="qr-textLabel">{{ __('ui.quiz.your_answer') }}</span>
                                        <span class="qr-textVal {{ $answer->is_correct ? 'qr-pass' : 'qr-fail' }}">
                                            {{ $answer->answer_text ?: '—' }}
                                        </span>
                                    </div>
                                    @if($correctOpts->isNotEmpty())
                                        <div class="qr-textRow">
                                            <span class="qr-textLabel">{{ __('ui.quiz.expected_answer') }}</span>
                                            <span class="qr-textVal qr-pass">
                                                {{ $correctOpts->pluck('option_text_display')->join(', ') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Explanation --}}
                            @if($question->explanation_display)
                                <div class="qr-expl">
                                    <span class="qr-explIcon">💡</span>
                                    <span>{{ $question->explanation_display }}</span>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- ── Actions ── --}}
            <div class="qr-actions">
                @php
                    $backUrl = route('my.courses');
                    if ($quiz->packItem?->pack && Route::has('courses.access')) {
                        $backUrl = route('courses.access', $quiz->packItem->pack);
                    } elseif ($quiz->course && Route::has('courses.access')) {
                        $backUrl = route('courses.access', $quiz->course);
                    }
                @endphp

                <a href="{{ $backUrl }}" class="qr-btn qr-btnGhost">{{ __('ui.quiz.back') }}</a>

                @if(!$quiz->max_attempts || $quiz->attempts()->where('user_id', auth()->id())->count() < $quiz->max_attempts)
                    <a href="{{ route('quiz.show', $quiz) }}" class="qr-btn qr-btnPrimary">
                        {{ __('ui.quiz.retry') }}
                    </a>
                @endif
            </div>
        </div>
    </x-container>

<style>
.qr-page{
    position:relative;
    min-height:calc(100vh - 80px);
    overflow:hidden;
    background:#07111d;
}
.qr-bg{
    position:absolute;inset:0;
    background:
        radial-gradient(900px 500px at 20% 10%, rgba(99,102,241,.18), transparent 60%),
        radial-gradient(700px 420px at 85% 20%, rgba(16,185,129,.14), transparent 55%),
        linear-gradient(180deg,#07111d 0%,#0b1726 100%);
}
.qr-shell{
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.055);
    backdrop-filter:blur(16px);
    border-radius:30px;
    padding:1.8rem;
    box-shadow:0 28px 70px rgba(0,0,0,.42);
}

/* ── Hero ── */
.qr-hero{
    display:flex;
    justify-content:space-between;
    gap:1.4rem;
    flex-wrap:wrap;
    margin-bottom:1.8rem;
}
.qr-badge{
    display:inline-block;
    border-radius:999px;
    padding:.4rem .9rem;
    font-size:.82rem;
    font-weight:700;
    margin-bottom:.75rem;
}
.qr-badge--pass{
    background:rgba(16,185,129,.18);
    border:1px solid rgba(16,185,129,.4);
    color:#6ee7b7;
}
.qr-badge--fail{
    background:rgba(244,63,94,.15);
    border:1px solid rgba(244,63,94,.35);
    color:#fda4af;
}
.qr-title{
    color:#fff;
    font-size:clamp(1.4rem,3vw,2.1rem);
    font-weight:900;
    line-height:1.12;
    letter-spacing:-.02em;
}
.qr-subtitle{
    margin-top:.5rem;
    color:rgba(255,255,255,.55);
    font-size:.9rem;
}

/* ── Score card ── */
.qr-scoreCard{
    display:flex;
    align-items:center;
    gap:1.2rem;
    min-width:min(100%,320px);
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.05);
    border-radius:24px;
    padding:1.2rem;
}
.qr-scoreCircle{
    position:relative;
    width:110px;height:110px;
    flex:0 0 110px;
}
.qr-scoreRing{
    width:110px;height:110px;
    transform-origin:center;
}
.qr-pct{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.5rem;
    font-weight:900;
    color:#fff;
}
.qr-scoreInfo strong{
    font-size:1.2rem;
    display:block;
    margin-bottom:.3rem;
}
.qr-scoreInfo p{
    color:rgba(255,255,255,.7);
    margin-bottom:.7rem;
    font-size:.9rem;
}
.qr-pass{ color:#34d399; }
.qr-fail{ color:#f87171; }
.qr-statsRow{ display:flex; gap:.5rem; flex-wrap:wrap; }
.qr-stat{
    font-size:.78rem;
    border-radius:999px;
    padding:.28rem .65rem;
    font-weight:700;
}
.qr-statGood{
    background:rgba(16,185,129,.15);
    border:1px solid rgba(16,185,129,.3);
    color:#6ee7b7;
}
.qr-statBad{
    background:rgba(244,63,94,.14);
    border:1px solid rgba(244,63,94,.3);
    color:#fca5a5;
}

/* ── Section ── */
.qr-sectionTitle{
    color:#fff;
    font-size:1.1rem;
    font-weight:800;
    margin-bottom:1rem;
    letter-spacing:-.01em;
}
.qr-list{ display:grid; gap:.9rem; }

/* ── Review item ── */
.qr-item{
    border-radius:20px;
    padding:1.2rem;
    border:1px solid rgba(255,255,255,.07);
    background:rgba(255,255,255,.03);
}
.qr-item.is-correct{
    border-color:rgba(16,185,129,.26);
    background:rgba(16,185,129,.06);
}
.qr-item.is-wrong{
    border-color:rgba(244,63,94,.26);
    background:rgba(244,63,94,.06);
}
.qr-itemHead{
    display:flex;
    align-items:flex-start;
    gap:.7rem;
    flex-wrap:wrap;
    margin-bottom:.9rem;
}
.qr-qNum{
    flex:0 0 auto;
    font-size:.75rem;
    border-radius:999px;
    padding:.28rem .62rem;
    background:rgba(255,255,255,.07);
    color:rgba(255,255,255,.65);
    border:1px solid rgba(255,255,255,.09);
    font-weight:600;
    margin-top:2px;
}
.qr-qText{
    flex:1;
    color:#fff;
    font-weight:700;
    font-size:.95rem;
    line-height:1.5;
}
.qr-state{
    flex:0 0 auto;
    border-radius:999px;
    padding:.28rem .65rem;
    font-size:.76rem;
    font-weight:700;
}
.qr-stateGood{
    background:rgba(16,185,129,.18);
    border:1px solid rgba(16,185,129,.35);
    color:#6ee7b7;
}
.qr-stateBad{
    background:rgba(244,63,94,.16);
    border:1px solid rgba(244,63,94,.32);
    color:#fca5a5;
}
.qr-qImg{
    margin-bottom:.9rem;
    border-radius:14px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.09);
    max-height:240px;
    display:flex;align-items:center;justify-content:center;
    background:rgba(0,0,0,.3);
}
.qr-qImg img{
    width:100%;
    max-height:240px;
    object-fit:contain;
}

/* ── Option list ── */
.qr-optList{ display:grid; gap:.45rem; }
.qr-opt{
    display:flex;
    align-items:center;
    gap:.65rem;
    padding:.7rem .85rem;
    border-radius:12px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.07);
    color:rgba(255,255,255,.75);
    font-size:.88rem;
}
.qr-optCorrect{
    background:rgba(16,185,129,.09);
    border-color:rgba(16,185,129,.3);
    color:#a7f3d0;
}
.qr-optWrong.qr-optSelected{
    background:rgba(244,63,94,.09);
    border-color:rgba(244,63,94,.3);
    color:#fca5a5;
}
.qr-optIcon{
    font-size:.9rem;
    flex:0 0 auto;
    width:20px;
    text-align:center;
}
.qr-optTag{
    margin-left:auto;
    font-size:.7rem;
    border-radius:999px;
    padding:.18rem .5rem;
    background:rgba(244,63,94,.18);
    color:#fca5a5;
    border:1px solid rgba(244,63,94,.3);
    white-space:nowrap;
}
.qr-optTagGood{
    background:rgba(16,185,129,.18);
    color:#6ee7b7;
    border-color:rgba(16,185,129,.35);
}

/* ── Matching table ── */
.qr-matchTable{
    width:100%;
    border-collapse:collapse;
    font-size:.85rem;
    margin-top:.4rem;
}
.qr-matchTable th{
    color:rgba(255,255,255,.5);
    text-align:left;
    padding:.4rem .6rem;
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    border-bottom:1px solid rgba(255,255,255,.08);
}
.qr-matchTable td{
    padding:.55rem .6rem;
    border-bottom:1px solid rgba(255,255,255,.05);
    color:rgba(255,255,255,.8);
}
.qr-trGood td{ color:#a7f3d0; }
.qr-trBad td{ color:#fca5a5; }

/* ── Ordering comparison ── */
.qr-orderComparison{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.8rem;
    margin-top:.4rem;
}
.qr-orderColHead{
    color:rgba(255,255,255,.5);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:.4rem;
    font-weight:600;
}
.qr-orderRow{
    display:flex;
    align-items:center;
    gap:.5rem;
    padding:.5rem .7rem;
    border-radius:10px;
    font-size:.85rem;
    margin-bottom:.35rem;
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.75);
}
.qr-orderRow.qr-trGood{
    background:rgba(16,185,129,.08);
    color:#a7f3d0;
}
.qr-orderRow.qr-trBad{
    background:rgba(244,63,94,.08);
    color:#fca5a5;
}

/* ── Text answer ── */
.qr-textAnswer{ display:grid; gap:.5rem; }
.qr-textRow{
    display:flex;
    align-items:baseline;
    gap:.6rem;
    flex-wrap:wrap;
}
.qr-textLabel{
    color:rgba(255,255,255,.55);
    font-size:.82rem;
    white-space:nowrap;
}
.qr-textVal{
    font-size:.9rem;
    font-weight:600;
}

/* ── Explanation ── */
.qr-expl{
    display:flex;
    gap:.6rem;
    margin-top:.9rem;
    border-top:1px solid rgba(255,255,255,.07);
    padding-top:.9rem;
    color:rgba(255,255,255,.72);
    line-height:1.6;
    font-size:.88rem;
}
.qr-explIcon{ flex:0 0 auto; font-size:1rem; }

/* ── Actions ── */
.qr-actions{
    display:flex;
    justify-content:flex-end;
    gap:.8rem;
    margin-top:1.4rem;
    flex-wrap:wrap;
}
.qr-btn{
    border-radius:14px;
    padding:.9rem 1.5rem;
    font-weight:700;
    text-decoration:none;
    font-size:.9rem;
    transition:.18s ease;
    display:inline-block;
}
.qr-btn:hover{ transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,0,0,.25); }
.qr-btnGhost{
    background:rgba(255,255,255,.07);
    color:rgba(255,255,255,.85);
    border:1px solid rgba(255,255,255,.10);
}
.qr-btnPrimary{
    background:linear-gradient(135deg,#6366f1 0%,#10b981 100%);
    color:#fff;
}

@media(max-width:640px){
    .qr-shell{ padding:1rem; border-radius:20px; }
    .qr-hero{ flex-direction:column; }
    .qr-scoreCard{ flex-direction:column; text-align:center; }
    .qr-statsRow{ justify-content:center; }
    .qr-orderComparison{ grid-template-columns:1fr; }
    .qr-actions{ flex-direction:column; }
    .qr-btn{ width:100%;text-align:center; }
}

/* ══════════════════════════════════════
   ANIMATIONS & EFFECTS
══════════════════════════════════════ */

/* ── Staggered card entrance ── */
@keyframes qr-stagger-in{
  from{opacity:0;transform:translateY(20px);}
  to{opacity:1;transform:translateY(0);}
}
.qr-item{
  opacity:0;
  animation:qr-stagger-in .42s ease forwards;
}

/* ── Score circle pulse after reveal ── */
@keyframes qr-ring-glow{
  0%{filter:drop-shadow(0 0 0px rgba(99,102,241,0));}
  50%{filter:drop-shadow(0 0 18px rgba(99,102,241,.7));}
  100%{filter:drop-shadow(0 0 0px rgba(99,102,241,0));}
}
.qr-scoreRing{animation:qr-ring-glow 1.2s ease 1.4s 1;}

/* ── Trophy badge pop-in ── */
@keyframes qr-badge-pop{
  0%{transform:scale(0) rotate(-12deg);opacity:0;}
  65%{transform:scale(1.18) rotate(4deg);opacity:1;}
  100%{transform:scale(1) rotate(0deg);opacity:1;}
}
.qr-badge--pass{animation:qr-badge-pop .55s cubic-bezier(.34,1.56,.64,1) .4s both;}

/* ── Floating emoji rain ── */
@keyframes qr-emoji-fall{
  0%{transform:translateY(-60px) rotate(0deg);opacity:1;}
  80%{opacity:.7;}
  100%{transform:translateY(110vh) rotate(360deg);opacity:0;}
}
.qr-emoji-particle{
  position:fixed;
  pointer-events:none;
  z-index:9998;
  animation:qr-emoji-fall linear forwards;
  will-change:transform;
}

/* ── Score number glow while counting ── */
@keyframes qr-count-glow{
  0%,100%{text-shadow:0 0 0 transparent;}
  50%{text-shadow:0 0 24px rgba(99,102,241,.8),0 0 40px rgba(16,185,129,.4);}
}
.qr-pct.counting{animation:qr-count-glow .6s ease infinite;}

/* ── Hero entrance ── */
@keyframes qr-hero-slide{
  from{opacity:0;transform:translateY(-18px);}
  to{opacity:1;transform:translateY(0);}
}
.qr-hero{animation:qr-hero-slide .5s ease both;}

/* ── Score card zoom-in ── */
@keyframes qr-card-zoom{
  from{opacity:0;transform:scale(.88);}
  to{opacity:1;transform:scale(1);}
}
.qr-scoreCard{animation:qr-card-zoom .55s cubic-bezier(.34,1.56,.64,1) .25s both;}
</style>

<script>
(function () {

/* ══════════════════════════════════════════════
   SFX ENGINE — Web Audio API (pas de CDN)
══════════════════════════════════════════════ */
const SFX = {
  _ctx: null,
  ctx() {
    if (!this._ctx) this._ctx = new (window.AudioContext || window.webkitAudioContext)();
    if (this._ctx.state === 'suspended') this._ctx.resume();
    return this._ctx;
  },
  _tone(freq, type, vol, dur, delay) {
    try {
      const c = this.ctx(), t = c.currentTime + (delay || 0);
      const o = c.createOscillator(), g = c.createGain();
      o.type = type || 'sine'; o.frequency.value = freq;
      o.connect(g); g.connect(c.destination);
      g.gain.setValueAtTime(0, t);
      g.gain.linearRampToValueAtTime(vol, t + .015);
      g.gain.exponentialRampToValueAtTime(.001, t + dur);
      o.start(t); o.stop(t + dur);
    } catch(e) {}
  },
  /* Fanfare — arpège C majeur ascendant */
  fanfare() {
    [523, 659, 784, 1047, 784, 1047].forEach((f, i) =>
      this._tone(f, 'triangle', .18, .38, i * .11));
  },
  /* Applaudissements — bruit blanc rythmé */
  applause() {
    try {
      const c = this.ctx();
      const dur = 3;
      const buf = c.createBuffer(1, Math.floor(c.sampleRate * dur), c.sampleRate);
      const d   = buf.getChannelData(0);
      for (let i = 0; i < d.length; i++) {
        const t   = i / c.sampleRate;
        const env = Math.min(1, t * 2.5) * Math.exp(-t * .55)
                  * (.55 + .45 * Math.abs(Math.sin(Math.PI * 7.5 * t)));
        d[i] = (Math.random() * 2 - 1) * env;
      }
      const src  = c.createBufferSource(); src.buffer = buf;
      const flt  = c.createBiquadFilter();
      flt.type = 'bandpass'; flt.frequency.value = 1300; flt.Q.value = .9;
      const gain = c.createGain();
      src.connect(flt); flt.connect(gain); gain.connect(c.destination);
      gain.gain.setValueAtTime(.32, c.currentTime);
      gain.gain.linearRampToValueAtTime(.48, c.currentTime + .35);
      gain.gain.linearRampToValueAtTime(.32, c.currentTime + 1.8);
      gain.gain.exponentialRampToValueAtTime(.001, c.currentTime + dur);
      src.start(); src.stop(c.currentTime + dur);
    } catch(e) {}
  },
  /* Notes tristes descendantes — échec */
  fail() {
    [392, 349, 330, 262].forEach((f, i) =>
      this._tone(f, 'sine', .12, .45, i * .19));
  },
  /* Tick pendant le compteur */
  tick() { this._tone(580, 'sine', .03, .04); },
};

const isPassed = @json($attempt->is_passed);
const finalPct = {{ number_format($attempt->percentage, 0) }};

/* ══════════════════════════════════════════════
   1. STAGGER ENTRANCE DES CARTES DE RÉVISION
══════════════════════════════════════════════ */
document.querySelectorAll('.qr-item').forEach((item, i) => {
  item.style.animationDelay = (.15 + i * .08) + 's';
});

/* ══════════════════════════════════════════════
   2. COMPTEUR DE SCORE ANIMÉ 0 → finalPct
══════════════════════════════════════════════ */
const pctEl = document.querySelector('.qr-pct');
if (pctEl) {
  pctEl.textContent = '0%';
  pctEl.classList.add('counting');
  let current = 0;
  const duration = 1500;
  const start    = performance.now();

  function countUp(now) {
    const progress = Math.min((now - start) / duration, 1);
    const eased    = 1 - Math.pow(1 - progress, 3);   // ease-out cubic
    const value    = Math.round(eased * finalPct);
    if (value !== current) {
      pctEl.textContent = value + '%';
      if (value % 5 === 0) SFX.tick();
      current = value;
    }
    if (progress < 1) { requestAnimationFrame(countUp); }
    else {
      pctEl.textContent = finalPct + '%';
      pctEl.classList.remove('counting');
    }
  }
  setTimeout(() => requestAnimationFrame(countUp), 350);
}

/* ══════════════════════════════════════════════
   3. CONFETTI + FEUX D'ARTIFICE (si réussi)
══════════════════════════════════════════════ */
if (isPassed) {
  const canvas = document.getElementById('confetti-canvas');
  if (canvas) {
    const ctx = canvas.getContext('2d');
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;

    const colors = ['#6366f1','#10b981','#eab308','#f43f5e','#38bdf8','#a78bfa','#fb923c','#34d399'];

    /* Confettis — rectangles + cercles + triangles */
    const pieces = Array.from({ length: 200 }, () => {
      const shapeN = Math.random();
      return {
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height - canvas.height,
        w: Math.random() * 13 + 5,
        h: Math.random() * 8 + 3,
        r: Math.random() * 7 + 3,
        color: colors[Math.floor(Math.random() * colors.length)],
        rot: Math.random() * 360,
        vx: (Math.random() - .5) * 4.5,
        vy: Math.random() * 3.5 + 1.5,
        vr: (Math.random() - .5) * 8,
        opacity: 1,
        shape: shapeN < .4 ? 'rect' : shapeN < .7 ? 'circle' : 'triangle',
      };
    });

    /* Feux d'artifice */
    const particles = [];
    function spawnFirework(cx, cy) {
      const c = colors[Math.floor(Math.random() * colors.length)];
      for (let i = 0; i < 48; i++) {
        const angle = (Math.PI * 2 * i) / 48;
        const spd   = Math.random() * 4.5 + 2;
        particles.push({
          x: cx, y: cy,
          vx: Math.cos(angle) * spd,
          vy: Math.sin(angle) * spd,
          color: c,
          size: Math.random() * 3.5 + 1.5,
          life: 1,
          decay: Math.random() * .022 + .014,
        });
      }
    }

    /* 3 explosions décalées */
    setTimeout(() => spawnFirework(canvas.width * .25, canvas.height * .3),  200);
    setTimeout(() => spawnFirework(canvas.width * .75, canvas.height * .25), 650);
    setTimeout(() => spawnFirework(canvas.width * .5,  canvas.height * .2),  1150);
    /* 2 extras si score > 80 */
    @if($attempt->percentage >= 80)
    setTimeout(() => spawnFirework(canvas.width * .15, canvas.height * .4),  1600);
    setTimeout(() => spawnFirework(canvas.width * .85, canvas.height * .35), 2000);
    @endif

    let frame = 0;
    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      /* Confettis */
      pieces.forEach(p => {
        p.x  += p.vx;
        p.y  += p.vy;
        p.rot += p.vr;
        p.vx += (Math.random() - .5) * .12; // légère turbulence
        if (frame > 90) p.opacity -= .009;
        ctx.save();
        ctx.globalAlpha = Math.max(0, p.opacity);
        ctx.fillStyle   = p.color;
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot * Math.PI / 180);
        if (p.shape === 'circle') {
          ctx.beginPath();
          ctx.arc(0, 0, p.r, 0, Math.PI * 2);
          ctx.fill();
        } else if (p.shape === 'triangle') {
          ctx.beginPath();
          ctx.moveTo(0, -p.r);
          ctx.lineTo(p.r * .87, p.r * .5);
          ctx.lineTo(-p.r * .87, p.r * .5);
          ctx.closePath(); ctx.fill();
        } else {
          ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
        }
        ctx.restore();
      });

      /* Feux d'artifice */
      for (let i = particles.length - 1; i >= 0; i--) {
        const fw = particles[i];
        fw.x    += fw.vx; fw.y += fw.vy;
        fw.vy   += .1;    // gravité
        fw.vx   *= .97;   fw.vy *= .97;
        fw.life -= fw.decay;
        ctx.save();
        ctx.globalAlpha = Math.max(0, fw.life);
        ctx.fillStyle   = fw.color;
        ctx.shadowColor = fw.color;
        ctx.shadowBlur  = 7;
        ctx.beginPath();
        ctx.arc(fw.x, fw.y, fw.size, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
        if (fw.life <= 0) particles.splice(i, 1);
      }

      frame++;
      if (frame < 220) { requestAnimationFrame(draw); }
      else { ctx.clearRect(0, 0, canvas.width, canvas.height); canvas.remove(); }
    }
    draw();
  }

  /* ──────────────────────────────────────
     Pluie d'emojis
  ────────────────────────────────────── */
  const emojiSet = ['🎉','🏆','⭐','🌟','✨','🎊','💫','🥳','🎈','👏'];
  for (let i = 0; i < 22; i++) {
    setTimeout(() => {
      const el = document.createElement('div');
      el.className = 'qr-emoji-particle';
      el.textContent = emojiSet[Math.floor(Math.random() * emojiSet.length)];
      el.style.left = (Math.random() * 100) + 'vw';
      el.style.top  = '-70px';
      el.style.fontSize = (Math.random() * 1.2 + 1.1) + 'rem';
      const dur = (Math.random() * 2.2 + 2.6).toFixed(2);
      el.style.animationDuration = dur + 's';
      document.body.appendChild(el);
      el.addEventListener('animationend', () => el.remove());
    }, i * 130);
  }

  /* ──────────────────────────────────────
     Sons : fanfare + applaudissements
  ────────────────────────────────────── */
  setTimeout(() => {
    @if($attempt->percentage >= 80)
      SFX.fanfare();
      setTimeout(() => SFX.applause(), 720);
    @else
      SFX.applause();
    @endif
  }, 450);

} else {
  /* ──────────────────────────────────────
     Échec : notes tristes
  ────────────────────────────────────── */
  setTimeout(() => SFX.fail(), 500);
}

})();
</script>
</div>
@endsection
