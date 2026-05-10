@extends('layouts.app')

@section('content')
@php
    $questions = $quiz->questions;
    $timeLimit = $quiz->time_limit_minutes;
@endphp

<div class="qa-page">
    <div class="qa-bg"></div>

    <x-container class="relative z-10 py-10">
        <div class="qa-shell">

            {{-- ── Header ── --}}
            <div class="qa-top">
                <div>
                    <div class="qa-badge">{{ __('ui.quiz.badge') }}</div>
                    <h1 class="qa-title">{{ $quiz->title_display }}</h1>
                    @if($quiz->description_display)
                        <p class="qa-subtitle">{{ $quiz->description_display }}</p>
                    @endif
                </div>

                <div class="qa-meta">
                    <div class="qa-metaCard">
                        <span>{{ __('ui.quiz.questions') }}</span>
                        <strong>{{ $questions->count() }}</strong>
                    </div>
                    <div class="qa-metaCard">
                        <span>{{ __('ui.quiz.threshold') }}</span>
                        <strong>{{ $quiz->passing_score }}%</strong>
                    </div>
                    <div class="qa-metaCard qa-timerCard {{ $timeLimit ? '' : 'qa-timerFree' }}">
                        <span>{{ __('ui.quiz.time') }}</span>
                        <strong id="quiz-timer">
                            {{ $timeLimit ? sprintf('%02d:00', $timeLimit) : __('ui.quiz.free_time') }}
                        </strong>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('quiz.submit', $quiz) }}" id="quiz-form">
                @csrf

                {{-- Progress bar --}}
                <div class="qa-progressWrap">
                    <div class="qa-progressBar">
                        <div class="qa-progressFill" id="progress-fill"></div>
                    </div>
                    <div class="qa-progressText">
                        <span id="progress-label">{{ __('ui.quiz.question_progress', ['current' => 1, 'total' => $questions->count()]) }}</span>
                    </div>
                </div>

                <div class="qa-questions">
                    @foreach($questions as $index => $question)
                        <section
                            class="qa-questionCard {{ $index === 0 ? 'is-active' : '' }}"
                            data-question-index="{{ $index }}"
                            data-type="{{ $question->question_type }}"
                        >
                            <div class="qa-questionHead">
                                <span class="qa-step">{{ $index + 1 }} / {{ $questions->count() }}</span>
                                <span class="qa-typeBadge qa-type-{{ $question->question_type }}">
                                    {{ match($question->question_type) {
                                        'single_choice'   => '🔘 ' . __('ui.quiz.type_single_choice'),
                                        'multiple_choice' => '☑️ ' . __('ui.quiz.type_multiple_choice'),
                                        'true_false'      => '✅ ' . __('ui.quiz.type_true_false'),
                                        'short_answer'    => '✏️ ' . __('ui.quiz.type_short_answer'),
                                        'fill_blank'      => '🧩 ' . __('ui.quiz.type_fill_blank'),
                                        'matching'        => '🔗 ' . __('ui.quiz.type_matching'),
                                        'ordering'        => '↕️ ' . __('ui.quiz.type_ordering'),
                                        default           => $question->question_type,
                                    } }}
                                </span>
                                <span class="qa-points">
                                    ⭐ {{ $question->points }} {{ $question->points > 1 ? __('ui.quiz.pts') : __('ui.quiz.pt') }}
                                </span>
                            </div>

                            {{-- Optional illustrative image --}}
                            @if($question->image_path)
                                <div class="qa-questionImg">
                                    <img
                                        src="{{ Storage::disk('public')->url($question->image_path) }}"
                                        alt="Illustration"
                                        loading="lazy"
                                    >
                                </div>
                            @endif

                            {{-- Question text --}}
                            @if($question->question_type === 'fill_blank')
                                <h2 class="qa-questionText">
                                    {!! preg_replace('/___/', '<span class="qa-blankSlot" id="slot-'.$question->id.'">___</span>', e($question->question_text_display)) !!}
                                </h2>
                            @else
                                <h2 class="qa-questionText">{{ $question->question_text_display }}</h2>
                            @endif

                            {{-- ═══════════════════════════════
                                 SINGLE CHOICE / TRUE_FALSE
                            ════════════════════════════════ --}}
                            @if(in_array($question->question_type, ['single_choice', 'true_false']))
                                <div class="qa-options" role="radiogroup">
                                    @foreach($question->options as $opt)
                                        <label class="qa-option" data-opt="{{ $opt->id }}">
                                            <input
                                                type="radio"
                                                name="answers[{{ $question->id }}]"
                                                value="{{ $opt->id }}"
                                                class="qa-optionInput"
                                            >
                                            <span class="qa-optionBox">
                                                <span class="qa-optionDot"></span>
                                                <span class="qa-optionLabel">{{ $opt->option_text_display }}</span>
                                                <span class="qa-optionPulse"></span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                            {{-- ═══════════════════════════════
                                 MULTIPLE CHOICE
                            ════════════════════════════════ --}}
                            @elseif($question->question_type === 'multiple_choice')
                                <p class="qa-hint">{{ __('ui.quiz.hint_multiple') }}</p>
                                <div class="qa-options" role="group">
                                    @foreach($question->options as $opt)
                                        <label class="qa-option qa-optionCheck" data-opt="{{ $opt->id }}">
                                            <input
                                                type="checkbox"
                                                name="answers[{{ $question->id }}][]"
                                                value="{{ $opt->id }}"
                                                class="qa-checkInput"
                                            >
                                            <span class="qa-optionBox">
                                                <span class="qa-checkBox">
                                                    <svg class="qa-checkIcon" viewBox="0 0 12 10" fill="none">
                                                        <path d="M1 5l3.5 3.5L11 1" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                                <span class="qa-optionLabel">{{ $opt->option_text_display }}</span>
                                                <span class="qa-optionPulse"></span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                            {{-- ═══════════════════════════════
                                 SHORT ANSWER
                            ════════════════════════════════ --}}
                            @elseif($question->question_type === 'short_answer')
                                <textarea
                                    name="answers[{{ $question->id }}]"
                                    rows="5"
                                    class="qa-textarea"
                                    placeholder="Écris ta réponse ici…"
                                ></textarea>

                            {{-- ═══════════════════════════════
                                 FILL BLANK — word bank chips
                            ════════════════════════════════ --}}
                            @elseif($question->question_type === 'fill_blank')
                                <div
                                    class="qa-wordBank"
                                    id="wordBank-{{ $question->id }}"
                                    data-qid="{{ $question->id }}"
                                >
                                    @foreach($question->options->shuffle() as $opt)
                                        <button
                                            type="button"
                                            class="qa-chip"
                                            data-word="{{ $opt->option_text_display }}"
                                            data-qid="{{ $question->id }}"
                                        >{{ $opt->option_text_display }}</button>
                                    @endforeach
                                </div>
                                <input
                                    type="hidden"
                                    name="answers[{{ $question->id }}]"
                                    id="fillInput-{{ $question->id }}"
                                >
                                <p class="qa-hint" style="margin-top:.6rem">{{ __('ui.quiz.hint_fill_blank') }}</p>

                            {{-- ═══════════════════════════════
                                 MATCHING — SVG lines
                            ════════════════════════════════ --}}
                            @elseif($question->question_type === 'matching')
                                @php $rightShuffled = $question->options->shuffle(); @endphp
                                <p class="qa-hint">{{ __('ui.quiz.hint_matching') }}</p>

                                <div
                                    class="qa-matchWrap"
                                    id="matchWrap-{{ $question->id }}"
                                    data-qid="{{ $question->id }}"
                                >
                                    <div class="qa-matchCol qa-matchLeft" id="matchLeft-{{ $question->id }}">
                                        @foreach($question->options as $opt)
                                            <div
                                                class="qa-matchItem"
                                                data-opt-id="{{ $opt->id }}"
                                                data-side="left"
                                                id="ml-{{ $opt->id }}"
                                            >{{ $opt->option_text_display }}</div>
                                        @endforeach
                                    </div>

                                    {{-- SVG canvas for lines --}}
                                    <svg
                                        class="qa-matchSvg"
                                        id="matchSvg-{{ $question->id }}"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                    ></svg>

                                    <div class="qa-matchCol qa-matchRight" id="matchRight-{{ $question->id }}">
                                        @foreach($rightShuffled as $opt)
                                            <div
                                                class="qa-matchItem"
                                                data-opt-id="{{ $opt->id }}"
                                                data-match-text="{{ $opt->match_text_display }}"
                                                data-side="right"
                                                id="mr-{{ $opt->id }}"
                                            >{{ $opt->match_text_display }}</div>
                                        @endforeach
                                    </div>
                                </div>

                                <div id="matchHiddens-{{ $question->id }}"></div>

                            {{-- ═══════════════════════════════
                                 ORDERING — up/down arrows
                            ════════════════════════════════ --}}
                            @elseif($question->question_type === 'ordering')
                                @php $shuffled = $question->options->shuffle(); @endphp
                                <p class="qa-hint">{{ __('ui.quiz.hint_ordering') }}</p>

                                <div
                                    class="qa-ordering"
                                    id="ordering-{{ $question->id }}"
                                    data-qid="{{ $question->id }}"
                                >
                                    @foreach($shuffled as $opt)
                                        <div class="qa-orderItem" data-opt-id="{{ $opt->id }}">
                                            <div class="qa-orderBtns">
                                                <button type="button" class="qa-orderBtn qa-orderUp" aria-label="Monter">▲</button>
                                                <button type="button" class="qa-orderBtn qa-orderDown" aria-label="Descendre">▼</button>
                                            </div>
                                            <span class="qa-orderNum"></span>
                                            <span class="qa-orderText">{{ $opt->option_text_display }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <input
                                    type="hidden"
                                    name="answers[{{ $question->id }}]"
                                    id="orderingInput-{{ $question->id }}"
                                >
                            @endif
                        </section>
                    @endforeach
                </div>

                <div class="qa-actions">
                    <button type="button" class="qa-btn qa-btnGhost" id="prev-btn" disabled>
                        {{ __('ui.quiz.prev') }}
                    </button>
                    <button type="button" class="qa-btn qa-btnPrimary" id="next-btn">
                        {{ __('ui.quiz.next') }}
                    </button>
                    <button type="submit" class="qa-btn qa-btnSuccess hidden" id="submit-btn">
                        {{ __('ui.quiz.finish') }}
                    </button>
                </div>
            </form>
        </div>
    </x-container>

{{-- ═══════════════════════════════════════════════════════════
     STYLES
════════════════════════════════════════════════════════════ --}}
<style>
/* ── Page ── */
.qa-page{
    position:relative;
    min-height:calc(100vh - 80px);
    background:#07111d;
    overflow:hidden;
}
.qa-bg{
    position:absolute;inset:0;
    background:
        radial-gradient(900px 500px at 12% 8%, rgba(99,102,241,.2), transparent 60%),
        radial-gradient(700px 400px at 88% 18%, rgba(16,185,129,.14), transparent 55%),
        radial-gradient(600px 350px at 50% 90%, rgba(234,179,8,.08), transparent 60%),
        linear-gradient(180deg,#07111d 0%,#0b1726 100%);
}

/* ── Shell ── */
.qa-shell{
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.055);
    backdrop-filter:blur(16px);
    border-radius:30px;
    padding:1.8rem;
    box-shadow:0 28px 70px rgba(0,0,0,.42);
}

/* ── Header ── */
.qa-top{
    display:flex;
    justify-content:space-between;
    gap:1.2rem;
    flex-wrap:wrap;
    margin-bottom:1.6rem;
}
.qa-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    border-radius:999px;
    padding:.35rem .8rem;
    background:rgba(99,102,241,.2);
    border:1px solid rgba(99,102,241,.4);
    color:#c7d2fe;
    font-size:.78rem;
    font-weight:700;
    margin-bottom:.8rem;
}
.qa-title{
    font-size:clamp(1.4rem,3vw,2.1rem);
    color:#fff;
    font-weight:900;
    line-height:1.12;
    letter-spacing:-.02em;
}
.qa-subtitle{
    margin-top:.55rem;
    color:rgba(255,255,255,.65);
    max-width:750px;
    line-height:1.65;
    font-size:.95rem;
}
.qa-meta{
    display:grid;
    grid-template-columns:repeat(3,minmax(100px,1fr));
    gap:.65rem;
    min-width:min(100%,360px);
}
.qa-metaCard{
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.05);
    border-radius:18px;
    padding:.75rem .9rem;
    transition:.2s ease;
}
.qa-metaCard span{
    display:block;
    color:rgba(255,255,255,.55);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    margin-bottom:.25rem;
}
.qa-metaCard strong{
    color:#fff;
    font-size:1rem;
    font-weight:800;
}
.qa-timerCard strong{ font-variant-numeric:tabular-nums; }

/* ── Progress ── */
.qa-progressWrap{ margin:1.2rem 0 1rem; }
.qa-progressBar{
    height:8px;
    border-radius:999px;
    background:rgba(255,255,255,.07);
    overflow:hidden;
}
.qa-progressFill{
    height:100%;
    width:0%;
    border-radius:999px;
    background:linear-gradient(90deg,#6366f1,#10b981,#eab308);
    transition:width .4s cubic-bezier(.4,0,.2,1);
}
.qa-progressText{
    display:flex;
    justify-content:flex-end;
    margin-top:.4rem;
    color:rgba(255,255,255,.5);
    font-size:.76rem;
    letter-spacing:.04em;
}

/* ── Question card ── */
.qa-questionCard{
    display:none;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(2,6,23,.3);
    border-radius:24px;
    padding:1.6rem;
    animation:fadeSlide .32s cubic-bezier(.4,0,.2,1);
}
.qa-questionCard.is-active{ display:block; }
@keyframes fadeSlide{
    from{opacity:0;transform:translateY(12px);}
    to{opacity:1;transform:translateY(0);}
}

/* ── Question head ── */
.qa-questionHead{
    display:flex;
    align-items:center;
    gap:.55rem;
    flex-wrap:wrap;
    margin-bottom:1rem;
}
.qa-step{
    font-size:.76rem;
    border-radius:999px;
    padding:.28rem .62rem;
    background:rgba(255,255,255,.06);
    color:rgba(255,255,255,.7);
    border:1px solid rgba(255,255,255,.09);
    font-weight:600;
}
.qa-points{
    font-size:.76rem;
    border-radius:999px;
    padding:.28rem .62rem;
    background:rgba(234,179,8,.12);
    color:#fde68a;
    border:1px solid rgba(234,179,8,.28);
    font-weight:700;
    margin-left:auto;
}
.qa-typeBadge{
    font-size:.73rem;
    border-radius:999px;
    padding:.28rem .65rem;
    font-weight:700;
}
.qa-type-single_choice,.qa-type-true_false{
    background:rgba(16,185,129,.15);
    border:1px solid rgba(16,185,129,.34);
    color:#6ee7b7;
}
.qa-type-multiple_choice{
    background:rgba(99,102,241,.16);
    border:1px solid rgba(99,102,241,.35);
    color:#a5b4fc;
}
.qa-type-matching{
    background:rgba(234,179,8,.13);
    border:1px solid rgba(234,179,8,.32);
    color:#fde68a;
}
.qa-type-ordering{
    background:rgba(239,68,68,.13);
    border:1px solid rgba(239,68,68,.30);
    color:#fca5a5;
}
.qa-type-fill_blank{
    background:rgba(14,165,233,.13);
    border:1px solid rgba(14,165,233,.30);
    color:#7dd3fc;
}
.qa-type-short_answer{
    background:rgba(168,85,247,.13);
    border:1px solid rgba(168,85,247,.30);
    color:#d8b4fe;
}

/* ── Question image ── */
.qa-questionImg{
    margin-bottom:1.1rem;
    border-radius:18px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.10);
    max-height:320px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(0,0,0,.3);
}
.qa-questionImg img{
    width:100%;
    height:100%;
    object-fit:contain;
    max-height:320px;
    display:block;
}

/* ── Question text ── */
.qa-questionText{
    color:#fff;
    font-size:1.15rem;
    line-height:1.55;
    font-weight:700;
    margin-bottom:1.1rem;
}
.qa-hint{
    color:rgba(255,255,255,.5);
    font-size:.83rem;
    margin-bottom:.9rem;
    font-style:italic;
}

/* ══════════════════════════════════════
   OPTIONS (radio / checkbox)
══════════════════════════════════════ */
.qa-options{ display:grid; gap:.7rem; }
.qa-option{ display:block; cursor:pointer; }
.qa-optionInput,.qa-checkInput{ display:none; }
.qa-optionBox{
    position:relative;
    display:flex;
    align-items:center;
    gap:.85rem;
    padding:.95rem 1rem;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.09);
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.88);
    transition:border-color .18s ease, background .18s ease, transform .14s ease, box-shadow .18s ease;
    overflow:hidden;
}
.qa-optionBox:hover{
    border-color:rgba(255,255,255,.2);
    background:rgba(255,255,255,.08);
    transform:translateX(3px);
}
.qa-optionLabel{ flex:1; font-size:.93rem; }

/* Pulse ring on select */
.qa-optionPulse{
    position:absolute;
    inset:0;
    border-radius:16px;
    pointer-events:none;
    opacity:0;
    background:rgba(99,102,241,.12);
    transition:opacity .25s ease;
}
.qa-optionInput:checked + .qa-optionBox .qa-optionPulse,
.qa-checkInput:checked + .qa-optionBox .qa-optionPulse{
    opacity:1;
}

/* Radio dot */
.qa-optionDot{
    width:20px;height:20px;
    border-radius:999px;
    border:2px solid rgba(255,255,255,.35);
    flex:0 0 20px;
    transition:.2s ease;
    position:relative;
}
.qa-optionDot::after{
    content:'';
    position:absolute;
    inset:3px;
    border-radius:999px;
    background:#6366f1;
    transform:scale(0);
    transition:transform .18s cubic-bezier(.34,1.56,.64,1);
}
.qa-optionInput:checked + .qa-optionBox{
    border-color:rgba(99,102,241,.55);
    background:rgba(99,102,241,.12);
    transform:translateX(4px);
    box-shadow:0 6px 20px rgba(99,102,241,.18);
}
.qa-optionInput:checked + .qa-optionBox .qa-optionDot{
    border-color:#6366f1;
}
.qa-optionInput:checked + .qa-optionBox .qa-optionDot::after{
    transform:scale(1);
}

/* Checkbox */
.qa-checkBox{
    width:22px;height:22px;
    border-radius:7px;
    border:2px solid rgba(255,255,255,.3);
    flex:0 0 22px;
    display:flex;align-items:center;justify-content:center;
    transition:.2s ease;
    background:transparent;
}
.qa-checkIcon{
    width:13px;height:11px;
    color:transparent;
    transition:.18s ease;
    transform:scale(0);
}
.qa-checkInput:checked + .qa-optionBox{
    border-color:rgba(99,102,241,.55);
    background:rgba(99,102,241,.12);
    transform:translateX(4px);
    box-shadow:0 6px 20px rgba(99,102,241,.18);
}
.qa-checkInput:checked + .qa-optionBox .qa-checkBox{
    background:#6366f1;
    border-color:#6366f1;
    box-shadow:0 0 0 4px rgba(99,102,241,.22);
}
.qa-checkInput:checked + .qa-optionBox .qa-checkIcon{
    color:#fff;
    transform:scale(1);
}

/* ══════════════════════════════════════
   TEXTAREA
══════════════════════════════════════ */
.qa-textarea{
    width:100%;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.11);
    background:rgba(255,255,255,.04);
    color:#fff;
    padding:1rem;
    outline:none;
    resize:vertical;
    font-size:.93rem;
    line-height:1.65;
    transition:.2s ease;
}
.qa-textarea:focus{
    border-color:rgba(168,85,247,.5);
    background:rgba(168,85,247,.07);
}

/* ══════════════════════════════════════
   FILL BLANK — word bank chips
══════════════════════════════════════ */
.qa-blankSlot{
    display:inline-block;
    min-width:90px;
    padding:0 6px;
    border-bottom:2.5px solid rgba(14,165,233,.7);
    color:rgba(14,165,233,1);
    font-style:normal;
    font-weight:800;
    cursor:pointer;
    border-radius:4px 4px 0 0;
    transition:.2s ease;
    vertical-align:middle;
}
.qa-blankSlot.has-word{
    background:rgba(14,165,233,.14);
    border-color:#38bdf8;
    color:#fff;
    padding:2px 10px;
    border-radius:8px;
    border-bottom-width:2.5px;
}
.qa-wordBank{
    display:flex;
    flex-wrap:wrap;
    gap:.55rem;
    margin-top:.5rem;
    padding:1rem;
    border:1px dashed rgba(14,165,233,.3);
    border-radius:16px;
    background:rgba(14,165,233,.04);
    min-height:56px;
}
.qa-chip{
    border:1px solid rgba(14,165,233,.4);
    background:rgba(14,165,233,.1);
    color:#7dd3fc;
    border-radius:999px;
    padding:.45rem 1rem;
    font-size:.88rem;
    font-weight:600;
    cursor:pointer;
    transition:.2s ease;
    user-select:none;
}
.qa-chip:hover{
    background:rgba(14,165,233,.22);
    border-color:rgba(14,165,233,.65);
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(14,165,233,.18);
}
.qa-chip.is-selected{
    background:#0ea5e9;
    border-color:#0ea5e9;
    color:#fff;
    transform:translateY(-2px);
    box-shadow:0 4px 14px rgba(14,165,233,.35);
}
.qa-chip.is-used{
    opacity:.3;
    cursor:not-allowed;
    transform:none;
}

/* ══════════════════════════════════════
   MATCHING — SVG lines
══════════════════════════════════════ */
.qa-matchWrap{
    display:grid;
    grid-template-columns:1fr 60px 1fr;
    gap:.5rem;
    margin-top:.6rem;
    position:relative;
    align-items:start;
}
.qa-matchCol{ display:flex; flex-direction:column; gap:.6rem; }
.qa-matchItem{
    padding:.85rem 1rem;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.09);
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.88);
    cursor:pointer;
    transition:.2s ease;
    font-size:.9rem;
    user-select:none;
    position:relative;
    text-align:center;
}
.qa-matchItem:hover{
    border-color:rgba(234,179,8,.45);
    background:rgba(234,179,8,.08);
}
.qa-matchItem.is-selected{
    border-color:rgba(234,179,8,.75);
    background:rgba(234,179,8,.15);
    box-shadow:0 0 0 3px rgba(234,179,8,.22), 0 6px 18px rgba(0,0,0,.25);
    transform:scale(1.02);
}
.qa-matchItem.is-matched{
    border-color:rgba(16,185,129,.55);
    background:rgba(16,185,129,.10);
    cursor:default;
}
.qa-matchItem.is-matched:hover{
    border-color:rgba(16,185,129,.55);
    background:rgba(16,185,129,.10);
    transform:none;
}
.qa-matchSvg{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    pointer-events:none;
    z-index:10;
    overflow:visible;
}
.qa-matchLine{
    stroke:url(#matchGrad);
    stroke-width:2.5;
    fill:none;
    stroke-linecap:round;
    stroke-dasharray:300;
    stroke-dashoffset:300;
    animation:drawLine .45s ease forwards;
}
@keyframes drawLine{
    to{ stroke-dashoffset:0; }
}
.qa-matchBadge{
    position:absolute;
    top:-7px;right:-7px;
    background:#10b981;
    border-radius:999px;
    width:18px;height:18px;
    display:flex;align-items:center;justify-content:center;
    font-size:.65rem;
    color:#fff;
    font-weight:700;
    border:2px solid #07111d;
    animation:popBadge .25s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popBadge{
    from{transform:scale(0);}
    to{transform:scale(1);}
}

/* ══════════════════════════════════════
   ORDERING
══════════════════════════════════════ */
.qa-ordering{
    display:flex;
    flex-direction:column;
    gap:.6rem;
    margin-top:.6rem;
}
.qa-orderItem{
    display:flex;
    align-items:center;
    gap:.8rem;
    padding:.85rem 1rem;
    border-radius:14px;
    border:1px solid rgba(239,68,68,.2);
    background:rgba(239,68,68,.05);
    color:rgba(255,255,255,.88);
    transition:.2s ease;
}
.qa-orderItem:hover{
    border-color:rgba(239,68,68,.4);
    background:rgba(239,68,68,.09);
}
.qa-orderBtns{
    display:flex;
    flex-direction:column;
    gap:2px;
}
.qa-orderBtn{
    background:rgba(255,255,255,.07);
    border:1px solid rgba(255,255,255,.11);
    border-radius:6px;
    color:#fff;
    width:28px;height:24px;
    cursor:pointer;
    font-size:.72rem;
    display:flex;align-items:center;justify-content:center;
    transition:.15s ease;
}
.qa-orderBtn:hover:not(:disabled){
    background:rgba(239,68,68,.25);
    border-color:rgba(239,68,68,.5);
    transform:scale(1.08);
}
.qa-orderBtn:disabled{
    opacity:.25;
    cursor:default;
}
.qa-orderNum{
    min-width:28px;
    text-align:center;
    font-weight:800;
    font-size:.95rem;
    color:rgba(239,68,68,.9);
}
.qa-orderText{ flex:1; font-size:.92rem; }

/* ── Actions ── */
.qa-actions{
    display:flex;
    justify-content:space-between;
    gap:.8rem;
    margin-top:1.4rem;
    flex-wrap:wrap;
}
.qa-btn{
    border:none;
    border-radius:14px;
    padding:.9rem 1.5rem;
    font-weight:700;
    font-size:.9rem;
    cursor:pointer;
    transition:.18s ease;
    letter-spacing:.01em;
}
.qa-btn:hover{ transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,0,0,.25); }
.qa-btnGhost{
    background:rgba(255,255,255,.07);
    color:rgba(255,255,255,.85);
    border:1px solid rgba(255,255,255,.10);
}
.qa-btnPrimary{
    background:linear-gradient(135deg,#6366f1 0%,#10b981 100%);
    color:#fff;
}
.qa-btnSuccess{
    background:linear-gradient(135deg,#10b981 0%,#059669 100%);
    color:#fff;
}
.hidden{ display:none!important; }

@media(max-width:640px){
    .qa-shell{ padding:1rem; border-radius:20px; }
    .qa-meta{ grid-template-columns:1fr 1fr; }
    .qa-matchWrap{ grid-template-columns:1fr 20px 1fr; gap:.3rem; }
    .qa-matchItem{ font-size:.8rem; padding:.65rem .6rem; }
    .qa-actions{ flex-direction:column; }
    .qa-btn{ width:100%;text-align:center; }
}
</style>

{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Navigation ── */
    const cards        = Array.from(document.querySelectorAll('.qa-questionCard'));
    const prevBtn      = document.getElementById('prev-btn');
    const nextBtn      = document.getElementById('next-btn');
    const submitBtn    = document.getElementById('submit-btn');
    const progressFill = document.getElementById('progress-fill');
    const progressLabel = document.getElementById('progress-label');
    const timerEl      = document.getElementById('quiz-timer');
    const form         = document.getElementById('quiz-form');
    let current = 0;
    const total = cards.length;
    const timeLimitMinutes = @json($timeLimit);

    function renderStep() {
        cards.forEach((c, i) => c.classList.toggle('is-active', i === current));
        prevBtn.disabled = current === 0;
        nextBtn.classList.toggle('hidden', current === total - 1);
        submitBtn.classList.toggle('hidden', current !== total - 1);
        progressFill.style.width = ((current + 1) / total * 100) + '%';
        progressLabel.textContent = @json(__('ui.quiz.question_progress')).replace(':current', current + 1).replace(':total', total);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    prevBtn?.addEventListener('click', () => { if (current > 0) { current--; renderStep(); } });
    nextBtn?.addEventListener('click', () => { if (current < total - 1) { current++; renderStep(); } });
    renderStep();

    /* ── Timer ── */
    if (timeLimitMinutes) {
        let s = timeLimitMinutes * 60;
        const iv = setInterval(() => {
            const m = Math.floor(s / 60), sec = s % 60;
            timerEl.textContent = String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
            if (timerEl.parentElement) {
                timerEl.parentElement.parentElement.style.borderColor =
                    s < 60 ? 'rgba(239,68,68,.55)' : '';
            }
            if (s-- <= 0) { clearInterval(iv); form.submit(); }
        }, 1000);
    }

    /* ══════════════════════════════════════════════
       WORD BANK — fill_blank
    ══════════════════════════════════════════════ */
    document.querySelectorAll('.qa-wordBank').forEach(bank => {
        const qid   = bank.dataset.qid;
        const input = document.getElementById('fillInput-' + qid);
        const slot  = document.getElementById('slot-' + qid);
        let selected = null;  // currently selected chip

        bank.querySelectorAll('.qa-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                if (chip.classList.contains('is-used')) return;

                if (selected === chip) {
                    // Deselect — clear blank
                    chip.classList.remove('is-selected');
                    selected = null;
                    if (slot) { slot.textContent = '___'; slot.classList.remove('has-word'); }
                    input.value = '';
                    return;
                }

                // Deselect previous
                if (selected) {
                    selected.classList.remove('is-selected');
                }

                chip.classList.add('is-selected');
                selected = chip;
                const word = chip.dataset.word;

                if (slot) {
                    slot.textContent = word;
                    slot.classList.add('has-word');
                }
                input.value = word;
            });
        });

        // Clicking the slot clears the selection
        if (slot) {
            slot.addEventListener('click', () => {
                if (selected) {
                    selected.classList.remove('is-selected');
                    selected = null;
                }
                slot.textContent = '___';
                slot.classList.remove('has-word');
                input.value = '';
            });
        }
    });

    /* ══════════════════════════════════════════════
       MATCHING — SVG animated lines
    ══════════════════════════════════════════════ */
    document.querySelectorAll('.qa-matchWrap').forEach(wrap => {
        const qid       = wrap.dataset.qid;
        const svg       = document.getElementById('matchSvg-' + qid);
        const hiddenDiv = document.getElementById('matchHiddens-' + qid);

        // Add SVG gradient def once
        const defs = document.createElementNS('http://www.w3.org/2000/svg','defs');
        defs.innerHTML = `
            <linearGradient id="matchGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#eab308"/>
                <stop offset="100%" stop-color="#10b981"/>
            </linearGradient>`;
        svg.appendChild(defs);

        let selectedLeft = null;   // { el, optId }
        const pairs = {};          // { leftOptId: matchText }
        const lines = {};          // { leftOptId: svgLine }

        function centerOf(el) {
            const wRect = wrap.getBoundingClientRect();
            const eRect = el.getBoundingClientRect();
            return {
                x: eRect.left - wRect.left + eRect.width / 2,
                y: eRect.top  - wRect.top  + eRect.height / 2,
            };
        }

        function drawLine(leftEl, rightEl, leftOptId) {
            const a = centerOf(leftEl);
            const b = centerOf(rightEl);
            const mx = (a.x + b.x) / 2;

            const line = document.createElementNS('http://www.w3.org/2000/svg','path');
            const d = `M ${a.x} ${a.y} C ${mx} ${a.y}, ${mx} ${b.y}, ${b.x} ${b.y}`;
            line.setAttribute('d', d);
            line.setAttribute('class','qa-matchLine');
            svg.appendChild(line);
            lines[leftOptId] = line;
        }

        function removeLine(leftOptId) {
            if (lines[leftOptId]) {
                lines[leftOptId].remove();
                delete lines[leftOptId];
            }
        }

        function updateHiddens() {
            hiddenDiv.innerHTML = '';
            Object.entries(pairs).forEach(([optId, matchText]) => {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = `answers[${qid}][${optId}]`;
                inp.value = matchText;
                hiddenDiv.appendChild(inp);
            });
        }

        wrap.querySelectorAll('[data-side="left"]').forEach(el => {
            el.addEventListener('click', () => {
                const optId = el.dataset.optId;

                if (selectedLeft) selectedLeft.el.classList.remove('is-selected');

                // Remove existing pair for this left item
                if (pairs[optId]) {
                    const oldRight = wrap.querySelector(`[data-side="right"][data-left-opt-id="${optId}"]`);
                    if (oldRight) {
                        oldRight.classList.remove('is-matched');
                        oldRight.dataset.leftOptId = '';
                        oldRight.querySelector('.qa-matchBadge')?.remove();
                    }
                    removeLine(optId);
                    delete pairs[optId];
                    updateHiddens();
                }

                el.classList.add('is-selected');
                selectedLeft = { el, optId };
            });
        });

        wrap.querySelectorAll('[data-side="right"]').forEach(el => {
            el.addEventListener('click', () => {
                if (!selectedLeft) return;

                // Remove old pair occupying this right item
                const oldLeftId = el.dataset.leftOptId;
                if (oldLeftId) {
                    const oldLeft = wrap.querySelector(`[data-side="left"][data-opt-id="${oldLeftId}"]`);
                    if (oldLeft) oldLeft.classList.remove('is-matched');
                    removeLine(oldLeftId);
                    delete pairs[oldLeftId];
                }

                const matchText = el.dataset.matchText;
                pairs[selectedLeft.optId] = matchText;
                el.dataset.leftOptId = selectedLeft.optId;

                selectedLeft.el.classList.remove('is-selected');
                selectedLeft.el.classList.add('is-matched');
                el.classList.add('is-matched');

                // Badge
                el.querySelector('.qa-matchBadge')?.remove();
                const badge = document.createElement('span');
                badge.className = 'qa-matchBadge';
                badge.textContent = '✓';
                el.appendChild(badge);

                // Draw SVG line
                drawLine(selectedLeft.el, el, selectedLeft.optId);

                selectedLeft = null;
                updateHiddens();
            });
        });
    });

    /* ══════════════════════════════════════════════
       ORDERING — up/down buttons
    ══════════════════════════════════════════════ */
    document.querySelectorAll('.qa-ordering').forEach(list => {
        const qid    = list.dataset.qid;
        const hidden = document.getElementById('orderingInput-' + qid);

        function refreshOrder() {
            const items = list.querySelectorAll('.qa-orderItem');
            const ids = [];
            items.forEach((item, i) => {
                item.querySelector('.qa-orderNum').textContent = (i + 1) + '.';
                item.querySelector('.qa-orderUp').disabled   = i === 0;
                item.querySelector('.qa-orderDown').disabled = i === items.length - 1;
                ids.push(parseInt(item.dataset.optId));
            });
            hidden.value = JSON.stringify(ids);
        }

        list.addEventListener('click', e => {
            const btn = e.target.closest('.qa-orderBtn');
            if (!btn || btn.disabled) return;
            const item = btn.closest('.qa-orderItem');
            if (btn.classList.contains('qa-orderUp') && item.previousElementSibling) {
                list.insertBefore(item, item.previousElementSibling);
            } else if (btn.classList.contains('qa-orderDown') && item.nextElementSibling) {
                list.insertBefore(item.nextElementSibling, item);
            }
            refreshOrder();
        });

        refreshOrder();
    });

});
</script>

{{-- ══════════════════════════════════════════════════════
     ANIMATIONS CSS
══════════════════════════════════════════════════════ --}}
<style>
/* ── Ripple on option select ── */
@keyframes qa-ripple{
  0%{transform:scale(0);opacity:.55;}
  100%{transform:scale(3.5);opacity:0;}
}
.qa-ripple-dot{
  position:absolute;
  border-radius:50%;
  pointer-events:none;
  width:36px;height:36px;
  margin:-18px 0 0 -18px;
  background:rgba(99,102,241,.35);
  animation:qa-ripple .55s ease-out forwards;
}

/* ── Float-up label ── */
@keyframes qa-float-up{
  0%{opacity:1;transform:translateY(0) scale(1);}
  60%{opacity:1;transform:translateY(-44px) scale(1.1);}
  100%{opacity:0;transform:translateY(-70px) scale(.75);}
}
.qa-float-label{
  position:fixed;
  pointer-events:none;
  font-size:1.2rem;
  font-weight:900;
  color:#fde68a;
  text-shadow:0 2px 10px rgba(0,0,0,.6);
  z-index:9999;
  animation:qa-float-up .9s cubic-bezier(.4,0,.2,1) forwards;
  white-space:nowrap;
}

/* ── Timer danger pulse ── */
@keyframes qa-timer-pulse{
  0%,100%{transform:scale(1);}
  50%{transform:scale(1.07);}
}
.qa-timer-danger{
  animation:qa-timer-pulse .55s ease infinite!important;
  border-color:rgba(239,68,68,.65)!important;
  background:rgba(239,68,68,.13)!important;
}
.qa-timer-danger strong{color:#f87171!important;}

/* ── Submit loading shimmer ── */
@keyframes qa-shimmer{
  0%{background-position:-200% center;}
  100%{background-position:200% center;}
}
.qa-btn-loading{
  background:linear-gradient(90deg,#6366f1,#10b981,#6366f1)!important;
  background-size:200% auto!important;
  animation:qa-shimmer 1s linear infinite!important;
  cursor:wait!important;
  pointer-events:none!important;
  opacity:.85!important;
}

/* ── Question slide transitions ── */
@keyframes qa-slide-right{
  from{opacity:0;transform:translateX(32px);}
  to{opacity:1;transform:translateX(0);}
}
@keyframes qa-slide-left{
  from{opacity:0;transform:translateX(-32px);}
  to{opacity:1;transform:translateX(0);}
}
.qa-questionCard.qa-slide-right{animation:qa-slide-right .3s cubic-bezier(.4,0,.2,1)!important;}
.qa-questionCard.qa-slide-left {animation:qa-slide-left  .3s cubic-bezier(.4,0,.2,1)!important;}

/* ── Progress bar glow ── */
.qa-progressFill{
  box-shadow:0 0 10px rgba(99,102,241,.55),0 0 22px rgba(16,185,129,.25);
}

/* ── Chip bounce on select ── */
@keyframes qa-chip-pop{
  0%{transform:translateY(0) scale(1);}
  45%{transform:translateY(-7px) scale(1.14);}
  100%{transform:translateY(-2px) scale(1.03);}
}
.qa-chip.is-selected{animation:qa-chip-pop .35s cubic-bezier(.34,1.56,.64,1) forwards!important;}

/* ── Match item success flash ── */
@keyframes qa-match-flash{
  0%{box-shadow:0 0 0 0 rgba(16,185,129,.7);}
  60%{box-shadow:0 0 0 10px rgba(16,185,129,0);}
  100%{box-shadow:0 0 0 0 rgba(16,185,129,0);}
}
.qa-matchItem.match-flash{animation:qa-match-flash .45s ease!important;}

/* ── Order item move flash ── */
@keyframes qa-order-flash{
  0%{background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.55);}
  100%{background:rgba(239,68,68,.05);border-color:rgba(239,68,68,.2);}
}
.qa-orderItem.qa-moved{animation:qa-order-flash .45s ease forwards!important;}

/* ── Stars particle ── */
@keyframes qa-star-burst{
  0%{transform:scale(0) rotate(0deg);opacity:1;}
  100%{transform:scale(1.8) rotate(45deg);opacity:0;}
}
.qa-star-particle{
  position:fixed;
  pointer-events:none;
  font-size:1.4rem;
  z-index:9999;
  animation:qa-star-burst .65s ease-out forwards;
}
</style>

{{-- ══════════════════════════════════════════════════════
     SOUND FX + ANIMATION HOOKS
══════════════════════════════════════════════════════ --}}
<script>
(function () {

/* ═══════════════════════════════════
   SFX — Web Audio API (no CDN)
═══════════════════════════════════ */
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
      g.gain.linearRampToValueAtTime(vol, t + 0.012);
      g.gain.exponentialRampToValueAtTime(0.001, t + dur);
      o.start(t); o.stop(t + dur);
    } catch(e) {}
  },
  /* Soft click for option selection */
  click() { this._tone(680, 'sine', .13, .09); },
  /* Pop for chips / matching selection */
  pop() {
    try {
      const c = this.ctx(), t = c.currentTime;
      const o = c.createOscillator(), g = c.createGain();
      o.type = 'sine';
      o.frequency.setValueAtTime(950, t);
      o.frequency.exponentialRampToValueAtTime(480, t + .08);
      o.connect(g); g.connect(c.destination);
      g.gain.setValueAtTime(.11, t);
      g.gain.exponentialRampToValueAtTime(.001, t + .1);
      o.start(t); o.stop(t + .1);
    } catch(e) {}
  },
  /* Double chime for match connection */
  match() {
    [523.25, 783.99].forEach((f, i) => this._tone(f, 'sine', .13, .3, i * .13));
  },
  /* Ascending sweep for next question */
  next() {
    try {
      const c = this.ctx(), t = c.currentTime;
      const o = c.createOscillator(), g = c.createGain();
      o.type = 'sine';
      o.frequency.setValueAtTime(340, t);
      o.frequency.exponentialRampToValueAtTime(680, t + .14);
      o.connect(g); g.connect(c.destination);
      g.gain.setValueAtTime(.09, t);
      g.gain.exponentialRampToValueAtTime(.001, t + .18);
      o.start(t); o.stop(t + .18);
    } catch(e) {}
  },
  /* Descending sweep for previous question */
  prev() {
    try {
      const c = this.ctx(), t = c.currentTime;
      const o = c.createOscillator(), g = c.createGain();
      o.type = 'sine';
      o.frequency.setValueAtTime(680, t);
      o.frequency.exponentialRampToValueAtTime(340, t + .14);
      o.connect(g); g.connect(c.destination);
      g.gain.setValueAtTime(.09, t);
      g.gain.exponentialRampToValueAtTime(.001, t + .18);
      o.start(t); o.stop(t + .18);
    } catch(e) {}
  },
  /* Soft thud for ordering */
  reorder() { this._tone(260, 'triangle', .08, .1); },
  /* Urgent tick for timer warning */
  warn() { this._tone(880, 'square', .045, .06); },
  /* 4-note ascending chime for submit */
  submit() {
    [523, 659, 784, 1047].forEach((f, i) => this._tone(f, 'sine', .15, .32, i * .1));
  },
};

/* ═══════════════════════════════════
   HELPERS
═══════════════════════════════════ */
function spawnRipple(el, e) {
  const rect = el.getBoundingClientRect();
  const dot  = document.createElement('span');
  dot.className = 'qa-ripple-dot';
  dot.style.left = ((e && e.clientX ? e.clientX - rect.left : rect.width / 2)) + 'px';
  dot.style.top  = ((e && e.clientY ? e.clientY - rect.top  : rect.height / 2)) + 'px';
  el.appendChild(dot);
  setTimeout(() => dot.remove(), 650);
}

function spawnFloatLabel(text, x, y) {
  const el = document.createElement('div');
  el.className = 'qa-float-label';
  el.textContent = text;
  el.style.left = x + 'px';
  el.style.top  = y + 'px';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 950);
}

function spawnStarBurst(x, y) {
  const stars = ['✨','⭐','💫','🌟'];
  for (let i = 0; i < 4; i++) {
    const el = document.createElement('div');
    el.className = 'qa-star-particle';
    el.textContent = stars[Math.floor(Math.random() * stars.length)];
    el.style.left = (x + (Math.random() - .5) * 60) + 'px';
    el.style.top  = (y + (Math.random() - .5) * 40) + 'px';
    el.style.animationDelay = (i * 0.07) + 's';
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 800);
  }
}

/* ═══════════════════════════════════
   HOOKS — runs after main DOMContentLoaded
═══════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

  /* ── Option boxes (radio + checkbox) ── */
  document.querySelectorAll('.qa-optionBox').forEach(box => {
    box.addEventListener('click', function (e) {
      SFX.click();
      spawnRipple(this, e);
    });
  });

  /* ── Next / Prev buttons — directional slide ── */
  const nextBtn   = document.getElementById('next-btn');
  const prevBtn   = document.getElementById('prev-btn');
  const submitBtn = document.getElementById('submit-btn');

  nextBtn?.addEventListener('click', function () {
    SFX.next();
    // Apply slide-right to the card that WILL become active
    requestAnimationFrame(() => {
      const active = document.querySelector('.qa-questionCard.is-active');
      if (active) {
        active.classList.remove('qa-slide-right', 'qa-slide-left');
        void active.offsetWidth;
        active.classList.add('qa-slide-right');
      }
    });
  });

  prevBtn?.addEventListener('click', function () {
    SFX.prev();
    requestAnimationFrame(() => {
      const active = document.querySelector('.qa-questionCard.is-active');
      if (active) {
        active.classList.remove('qa-slide-right', 'qa-slide-left');
        void active.offsetWidth;
        active.classList.add('qa-slide-left');
      }
    });
  });

  /* ── Submit: chime + shimmer + stars ── */
  submitBtn?.addEventListener('click', function (e) {
    SFX.submit();
    this.classList.add('qa-btn-loading');
    this.textContent = '⏳ Envoi…';
    const r = this.getBoundingClientRect();
    spawnStarBurst(r.left + r.width / 2, r.top);
  });

  /* ── Word bank chips ── */
  document.querySelectorAll('.qa-chip').forEach(chip => {
    chip.addEventListener('click', function () {
      SFX.pop();
    });
  });

  /* ── Matching items ── */
  document.querySelectorAll('[data-side="right"]').forEach(el => {
    // Observe class changes → play match sound when matched
    const obs = new MutationObserver(muts => {
      muts.forEach(m => {
        if (m.target.classList.contains('is-matched')) {
          SFX.match();
          m.target.classList.remove('match-flash');
          void m.target.offsetWidth;
          m.target.classList.add('match-flash');
        }
      });
    });
    obs.observe(el, { attributes: true, attributeFilter: ['class'] });

    // Click on non-matched: pop
    el.addEventListener('click', function () {
      if (!this.classList.contains('is-matched')) SFX.pop();
    });
  });

  document.querySelectorAll('[data-side="left"]').forEach(el => {
    el.addEventListener('click', () => SFX.click());
  });

  /* ── Ordering buttons ── */
  document.querySelectorAll('.qa-orderBtn').forEach(btn => {
    btn.addEventListener('click', function () {
      if (this.disabled) return;
      SFX.reorder();
      const item = this.closest('.qa-orderItem');
      if (item) {
        item.classList.remove('qa-moved');
        void item.offsetWidth;
        item.classList.add('qa-moved');
      }
    });
  });

  /* ── Timer: danger animation + warning sounds ── */
  const timerEl = document.getElementById('quiz-timer');
  if (timerEl) {
    const timerCard = timerEl.closest('.qa-metaCard');
    let lastWarn = -1;

    const obs = new MutationObserver(() => {
      const parts = timerEl.textContent.trim().split(':');
      if (parts.length < 2) return;
      const secs = parseInt(parts[0]) * 60 + parseInt(parts[1]);
      if (secs <= 30 && timerCard) timerCard.classList.add('qa-timer-danger');
      // Warn: every second below 10, every 5s below 30
      if (secs <= 30 && secs !== lastWarn) {
        if (secs <= 10 || secs % 5 === 0) { SFX.warn(); lastWarn = secs; }
      }
    });
    obs.observe(timerEl, { childList: true, subtree: true, characterData: true });
  }

});

})();
</script>
</div>
@endsection
