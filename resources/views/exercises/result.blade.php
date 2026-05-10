@extends('layouts.app')

@section('content')
  @php
    $title      = $exercise->title_display ?? $exercise->title;
    $pct        = $attempt->percentage;
    $items      = $exercise->items->sortBy('sort_order')->values();

    // Map item answers by item_id for fast lookup
    $attemptAnswers = $attempt->answers->keyBy('exercise_item_id');

    $scoreColor = match(true) {
        $pct >= 80 => '#10b981',
        $pct >= 50 => '#f59e0b',
        default    => '#ef4444',
    };
    $scoreEmoji = match(true) {
        $pct >= 80 => '🎉',
        $pct >= 50 => '👍',
        default    => '💪',
    };
    $scoreMsg = match(true) {
        $pct >= 80 => __('ui.quiz.score_excellent'),
        $pct >= 50 => __('ui.quiz.score_good'),
        default    => __('ui.quiz.score_practice'),
    };
  @endphp

  <div class="za-exPage">
    <div class="za-exBg"></div>

    <x-container class="py-10 relative z-10">

      {{-- Navigation --}}
      <div class="flex items-center justify-between mb-8">
        <a href="javascript:history.back()" class="za-exBackBtn">{{ __('ui.quiz.back_btn') }}</a>
        <span class="za-exMetaTag" style="background:rgba(255,255,255,.06)">{{ __('ui.quiz.results_badge') }}</span>
      </div>

      {{-- Score card --}}
      <div class="za-resScoreCard" style="--score-color:{{ $scoreColor }}">
        <div class="za-resScoreEmoji">{{ $scoreEmoji }}</div>
        <h1 class="za-resTitle">{{ $title }}</h1>
        <p class="za-resMsg">{{ $scoreMsg }}</p>

        <div class="za-resScoreNum">
          <span style="color:{{ $scoreColor }};font-size:3rem;font-weight:900;">{{ $attempt->score }}</span>
          <span style="color:rgba(255,255,255,.4);font-size:1.5rem;font-weight:700;"> / {{ $attempt->max_score }}</span>
          <span style="color:rgba(255,255,255,.5);font-size:1rem;margin-left:.5rem;">pts</span>
        </div>

        <div class="za-resBarWrap">
          <div class="za-resBar">
            <div class="za-resFill" style="width:{{ $pct }}%;background:{{ $scoreColor }}"></div>
          </div>
          <span class="za-resPct" style="color:{{ $scoreColor }}">{{ $pct }}%</span>
        </div>

        <div class="za-resStats">
          <div class="za-resStat">
            <span class="za-resStatVal" style="color:#10b981">{{ $attempt->answers->where('is_correct', true)->count() }}</span>
            <span class="za-resStatLabel">{{ __('ui.quiz.stat_correct') }}</span>
          </div>
          <div class="za-resStat">
            <span class="za-resStatVal" style="color:#ef4444">{{ $attempt->answers->where('is_correct', false)->count() }}</span>
            <span class="za-resStatLabel">{{ __('ui.quiz.stat_wrong') }}</span>
          </div>
          <div class="za-resStat">
            <span class="za-resStatVal">{{ $items->count() }}</span>
            <span class="za-resStatLabel">{{ __('ui.quiz.stat_total') }}</span>
          </div>
        </div>
      </div>

      {{-- Correction per item --}}
      <div class="mt-8 space-y-5">
        @foreach($items as $idx => $item)
          @php
            $attemptAnswer  = $attemptAnswers->get($item->id);
            $isCorrect      = $attemptAnswer?->is_correct ?? false;
            $pointsEarned   = $attemptAnswer?->points_earned ?? 0;
            $answerData     = $attemptAnswer?->answer_data ?? [];
            $answers        = $item->answers->sortBy('sort_order')->values();

            $typeLabel = match($item->item_type) {
              'single_choice'   => __('ui.quiz.type_single_choice'),
              'multiple_choice' => __('ui.quiz.type_multiple_choice'),
              'true_false'      => __('ui.quiz.type_true_false'),
              'matching'        => __('ui.quiz.type_matching'),
              'ordering'        => __('ui.quiz.type_ordering'),
              'fill_blank'      => __('ui.quiz.type_fill_blank_ex'),
              'word_bank'       => __('ui.quiz.type_word_bank'),
              'categorization'  => __('ui.quiz.type_categorization'),
              'short_answer'    => __('ui.quiz.type_short_answer'),
              'calculation'     => __('ui.quiz.type_calculation'),
              default           => $item->item_type,
            };
          @endphp

          <div class="za-resItem {{ $isCorrect ? 'is-correct' : 'is-wrong' }}">
            <div class="za-resItemTop">
              <div class="za-resItemLeft">
                <span class="za-resItemNum">{{ __('ui.quiz.q_prefix') }}{{ $idx + 1 }}</span>
                <span class="za-resItemType">{{ $typeLabel }}</span>
              </div>
              <div class="za-resItemRight">
                @if($isCorrect)
                  <span class="za-resBadge za-resBadgeOk">✅ +{{ $pointsEarned }} pt</span>
                @else
                  <span class="za-resBadge za-resBadgeWrong">❌ 0 / {{ $item->points }} pt</span>
                @endif
              </div>
            </div>

            @if($item->image_path)
              <img src="{{ asset('storage/' . $item->image_path) }}" class="za-exItemImage" alt="">
            @endif
            <p class="za-resStatement">{!! nl2br(e($item->statement ?? '')) !!}</p>

            {{-- User answer display --}}
            <div class="za-resAnswerBlock">
              <div class="za-resAnswerLabel {{ $isCorrect ? 'ok' : 'wrong' }}">
                {{ $isCorrect ? __('ui.quiz.your_answer_ok') : __('ui.quiz.your_answer_ko') }}
              </div>
              <div class="za-resAnswerValue">
                @if(in_array($item->item_type, ['single_choice','true_false','multiple_choice']))
                  @php
                    $ids = isset($answerData['answer_id'])
                         ? [$answerData['answer_id']]
                         : ($answerData['answer_ids'] ?? []);
                    $selectedAnswers = $answers->whereIn('id', $ids);
                  @endphp
                  @if($selectedAnswers->isEmpty())
                    <em style="color:rgba(255,255,255,.35)">{{ __('ui.quiz.no_answer') }}</em>
                  @else
                    @foreach($selectedAnswers as $a) <span class="za-resPill">{{ $a->answer_text }}</span> @endforeach
                  @endif

                @elseif($item->item_type === 'matching')
                  @php $pairs = $answerData['pairs'] ?? []; @endphp
                  @foreach($answers as $a)
                    <div class="za-resPairRow">
                      <span class="za-resPill">{{ $a->answer_text }}</span>
                      <span style="color:rgba(255,255,255,.3)">→</span>
                      <span class="za-resPill {{ strtolower(trim($pairs[$a->id] ?? '')) === strtolower(trim($a->match_text ?? '')) ? 'ok' : 'wrong' }}">
                        {{ $pairs[$a->id] ?? '—' }}
                      </span>
                    </div>
                  @endforeach

                @elseif($item->item_type === 'ordering')
                  @php $positions = $answerData['positions'] ?? []; @endphp
                  @php
                    $userOrdered = $answers->map(fn($a) => [
                      'text' => $a->answer_text,
                      'user_pos' => (int)($positions[$a->id] ?? 0),
                      'correct_pos' => (int)$a->sort_order,
                    ])->sortBy('user_pos')->values();
                  @endphp
                  @foreach($userOrdered as $row)
                    <div class="za-resPairRow">
                      <span class="za-resPill" style="min-width:30px;text-align:center">{{ $row['user_pos'] ?: '?' }}</span>
                      <span class="za-resPill {{ $row['user_pos'] === $row['correct_pos'] ? 'ok' : 'wrong' }}">
                        {{ $row['text'] }}
                      </span>
                    </div>
                  @endforeach

                @elseif($item->item_type === 'fill_blank')
                  @php $blanks = $answerData['blanks'] ?? []; @endphp
                  @foreach($blanks as $bIdx => $val)
                    <span class="za-resPill">{{ __('ui.quiz.blank_label', ['num' => $bIdx + 1]) }} {{ $val ?: '—' }}</span>
                  @endforeach

                @elseif($item->item_type === 'word_bank')
                  @php
                    $selectedIds = $answerData['answer_ids'] ?? [];
                    $selected = $answers->whereIn('id', $selectedIds);
                  @endphp
                  @foreach($selected as $a) <span class="za-resPill">{{ $a->answer_text }}</span> @endforeach
                  @if($selected->isEmpty()) <em style="color:rgba(255,255,255,.35)">{{ __('ui.quiz.no_word_selected') }}</em> @endif

                @elseif($item->item_type === 'categorization')
                  @php $cats = $answerData['categories'] ?? []; @endphp
                  @foreach($answers as $a)
                    <div class="za-resPairRow">
                      <span class="za-resPill">{{ $a->answer_text }}</span>
                      <span style="color:rgba(255,255,255,.3)">→</span>
                      <span class="za-resPill {{ strtolower(trim($cats[$a->id] ?? '')) === strtolower(trim($a->category ?? '')) ? 'ok' : 'wrong' }}">
                        {{ $cats[$a->id] ?? '—' }}
                      </span>
                    </div>
                  @endforeach

                @elseif($item->item_type === 'short_answer')
                  <span class="za-resPill">{{ $answerData['text'] ?? '—' }}</span>

                @elseif($item->item_type === 'calculation')
                  <span class="za-resPill">{{ $answerData['value'] ?? '—' }}</span>

                @else
                  <span style="color:rgba(255,255,255,.4)">{{ json_encode($answerData) }}</span>
                @endif
              </div>
            </div>

            {{-- Correct answer (only if wrong) --}}
            @if(!$isCorrect)
              <div class="za-resCorrectBlock">
                <div class="za-resAnswerLabel ok">{{ __('ui.quiz.correct_answer') }}</div>
                <div class="za-resAnswerValue">

                  @if(in_array($item->item_type, ['single_choice','true_false','multiple_choice']))
                    @foreach($answers->where('is_correct', true) as $a)
                      <span class="za-resPill ok">{{ $a->answer_text }}</span>
                    @endforeach

                  @elseif($item->item_type === 'matching')
                    @foreach($answers as $a)
                      <div class="za-resPairRow">
                        <span class="za-resPill">{{ $a->answer_text }}</span>
                        <span style="color:rgba(255,255,255,.3)">→</span>
                        <span class="za-resPill ok">{{ $a->match_text }}</span>
                      </div>
                    @endforeach

                  @elseif($item->item_type === 'ordering')
                    @foreach($answers->sortBy('sort_order') as $a)
                      <div class="za-resPairRow">
                        <span class="za-resPill ok" style="min-width:30px;text-align:center">{{ $a->sort_order }}</span>
                        <span class="za-resPill">{{ $a->answer_text }}</span>
                      </div>
                    @endforeach

                  @elseif($item->item_type === 'fill_blank')
                    @foreach($answers->where('is_correct', true)->sortBy('sort_order') as $i => $a)
                      <span class="za-resPill ok">{{ __('ui.quiz.blank_label', ['num' => $i + 1]) }} {{ $a->answer_text }}</span>
                    @endforeach

                  @elseif($item->item_type === 'word_bank')
                    @foreach($answers->where('is_correct', true) as $a)
                      <span class="za-resPill ok">{{ $a->answer_text }}</span>
                    @endforeach

                  @elseif($item->item_type === 'categorization')
                    @foreach($answers as $a)
                      <div class="za-resPairRow">
                        <span class="za-resPill">{{ $a->answer_text }}</span>
                        <span style="color:rgba(255,255,255,.3)">→</span>
                        <span class="za-resPill ok">{{ $a->category }}</span>
                      </div>
                    @endforeach

                  @elseif($item->item_type === 'short_answer')
                    @foreach($answers->where('is_correct', true) as $a)
                      <span class="za-resPill ok">{{ $a->answer_text }}</span>
                    @endforeach

                  @elseif($item->item_type === 'calculation')
                    @php $calcAns = $answers->first(); @endphp
                    @if($calcAns)
                      <span class="za-resPill ok">
                        {{ $calcAns->correct_value }}
                        @if((float)$calcAns->tolerance > 0) ± {{ $calcAns->tolerance }} @endif
                      </span>
                    @endif
                  @endif

                </div>
              </div>
            @endif

            {{-- Explanation --}}
            @if($item->explanation)
              <div class="za-resExplanation">
                <span class="za-resExplIcon">💡</span>
                <p>{{ $item->explanation }}</p>
              </div>
            @endif
          </div>
        @endforeach
      </div>

      {{-- Bottom actions --}}
      <div class="za-resActions">
        @if($exercise->allow_retry)
          <a href="{{ route('exercise.show', $exercise) }}" class="za-exSubmitBtn" style="text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;">
            {{ __('ui.quiz.restart') }}
          </a>
        @endif
        <a href="javascript:history.back()" class="za-exBackBtn" style="padding:.9rem 1.5rem;">
          {{ __('ui.quiz.back_to_course') }}
        </a>
      </div>

    </x-container>
  </div>

  <style>
    /* ── Reuse base styles from show.blade.php ───────────────── */
    .za-exPage { min-height: 100vh; position: relative; color: #f1f5f9; }
    .za-exBg {
      position: fixed; inset: 0; z-index: 0;
      background:
        radial-gradient(900px 500px at 10% 5%, rgba(16,185,129,.14), transparent 60%),
        radial-gradient(700px 400px at 90% 90%, rgba(99,102,241,.12), transparent 55%),
        linear-gradient(160deg, #0b1a28 0%, #07111d 100%);
    }
    .za-exBackBtn {
      color: rgba(255,255,255,.6); font-size: .85rem; font-weight: 600;
      text-decoration: none; border: 1px solid rgba(255,255,255,.12);
      border-radius: 999px; padding: .45rem .9rem; transition: .15s;
    }
    .za-exBackBtn:hover { color: #fff; border-color: rgba(255,255,255,.25); }
    .za-exMetaTag {
      font-size: .78rem; font-weight: 700; border-radius: 999px;
      padding: .3rem .75rem; background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.8);
    }
    .za-exItemImage {
      width: 100%; max-height: 200px; object-fit: contain;
      border-radius: 12px; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,.08);
    }
    .za-exSubmitBtn {
      background: linear-gradient(135deg, #10b981, #059669);
      color: #fff; font-size: 1rem; font-weight: 900;
      border: none; border-radius: 16px; padding: .9rem 2rem;
      cursor: pointer; transition: .18s;
      box-shadow: 0 10px 28px rgba(16,185,129,.3);
    }
    .za-exSubmitBtn:hover { filter: brightness(1.07); transform: translateY(-1px); }

    /* ── Score card ──────────────────────────────────────────── */
    .za-resScoreCard {
      border-radius: 26px;
      border: 1px solid rgba(255,255,255,.1);
      background: rgba(255,255,255,.04);
      backdrop-filter: blur(8px);
      padding: 2.5rem;
      text-align: center;
    }
    .za-resScoreEmoji { font-size: 2.8rem; margin-bottom: .5rem; }
    .za-resTitle { font-size: 1.3rem; font-weight: 900; color: #fff; margin-bottom: .3rem; }
    .za-resMsg { color: rgba(255,255,255,.55); font-size: .9rem; margin-bottom: 1.5rem; }
    .za-resScoreNum { margin-bottom: 1.2rem; line-height: 1; }
    .za-resBarWrap {
      display: flex; align-items: center; gap: .85rem;
      max-width: 380px; margin: 0 auto 1.5rem;
    }
    .za-resBar {
      flex: 1; height: 10px; border-radius: 999px;
      background: rgba(255,255,255,.08); overflow: hidden;
    }
    .za-resFill { height: 100%; border-radius: 999px; transition: width .6s ease; }
    .za-resPct { font-size: 1rem; font-weight: 900; white-space: nowrap; }
    .za-resStats {
      display: flex; justify-content: center; gap: 2rem;
      flex-wrap: wrap;
    }
    .za-resStat { display: flex; flex-direction: column; align-items: center; gap: .2rem; }
    .za-resStatVal { font-size: 1.5rem; font-weight: 900; }
    .za-resStatLabel { font-size: .78rem; color: rgba(255,255,255,.4); font-weight: 600; }

    /* ── Item correction card ────────────────────────────────── */
    .za-resItem {
      border-radius: 20px;
      border: 1px solid rgba(255,255,255,.07);
      background: rgba(255,255,255,.035);
      padding: 1.4rem;
    }
    .za-resItem.is-correct { border-left: 3px solid #10b981; }
    .za-resItem.is-wrong   { border-left: 3px solid #ef4444; }

    .za-resItemTop {
      display: flex; align-items: center;
      justify-content: space-between; gap: 1rem;
      margin-bottom: .9rem; flex-wrap: wrap;
    }
    .za-resItemLeft { display: flex; align-items: center; gap: .6rem; }
    .za-resItemNum { font-weight: 900; color: rgba(255,255,255,.4); font-size: .9rem; }
    .za-resItemType {
      font-size: .75rem; font-weight: 700; color: rgba(255,255,255,.5);
      border: 1px solid rgba(255,255,255,.1); border-radius: 999px; padding: .2rem .6rem;
    }
    .za-resBadge {
      font-size: .8rem; font-weight: 800; border-radius: 999px; padding: .3rem .75rem;
    }
    .za-resBadgeOk   { background: rgba(16,185,129,.15); color: #10b981; }
    .za-resBadgeWrong{ background: rgba(239,68,68,.12);  color: #ef4444; }

    .za-resStatement {
      color: rgba(255,255,255,.9); font-size: .95rem;
      line-height: 1.65; margin-bottom: 1rem;
    }

    /* ── Answer blocks ───────────────────────────────────────── */
    .za-resAnswerBlock, .za-resCorrectBlock {
      border-radius: 14px;
      padding: .9rem 1rem;
      margin-bottom: .75rem;
    }
    .za-resAnswerBlock { background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.15); }
    .za-resAnswerBlock:has(.za-resAnswerLabel.ok) {
      background: rgba(16,185,129,.06);
      border-color: rgba(16,185,129,.18);
    }
    .za-resCorrectBlock { background: rgba(16,185,129,.06); border: 1px solid rgba(16,185,129,.18); }

    .za-resAnswerLabel {
      font-size: .78rem; font-weight: 800; margin-bottom: .5rem; text-transform: uppercase;
      letter-spacing: .04em;
    }
    .za-resAnswerLabel.ok   { color: #10b981; }
    .za-resAnswerLabel.wrong{ color: #ef4444; }

    .za-resAnswerValue {
      display: flex; flex-wrap: wrap; gap: .45rem; align-items: center;
    }

    /* ── Pills ───────────────────────────────────────────────── */
    .za-resPill {
      display: inline-block;
      font-size: .85rem; font-weight: 600;
      border-radius: 999px; padding: .3rem .75rem;
      background: rgba(255,255,255,.08);
      color: rgba(255,255,255,.85);
      border: 1px solid rgba(255,255,255,.1);
    }
    .za-resPill.ok   { background: rgba(16,185,129,.15); border-color: rgba(16,185,129,.3); color: #6ee7b7; }
    .za-resPill.wrong{ background: rgba(239,68,68,.12);  border-color: rgba(239,68,68,.25); color: #fca5a5; }

    .za-resPairRow { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; width: 100%; }

    /* ── Explanation ─────────────────────────────────────────── */
    .za-resExplanation {
      display: flex; gap: .75rem; align-items: flex-start;
      margin-top: .75rem;
      border-radius: 12px; border: 1px solid rgba(234,179,8,.18);
      background: rgba(234,179,8,.05);
      padding: .8rem 1rem;
      font-size: .87rem;
      color: rgba(255,255,255,.75);
      line-height: 1.6;
    }
    .za-resExplIcon { font-size: 1rem; flex-shrink: 0; }

    /* ── Bottom actions ──────────────────────────────────────── */
    .za-resActions {
      display: flex; justify-content: center;
      gap: 1rem; flex-wrap: wrap;
      padding: 2rem 0;
    }
  </style>
@endsection
