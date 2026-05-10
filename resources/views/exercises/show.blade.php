@extends('layouts.app')

@section('content')
  @php
    use Illuminate\Support\Facades\Storage;

    $title       = $exercise->title_display ?? $exercise->title;
    $description = $exercise->description ?? null;
    $instructions= $exercise->instructions ?? null;
    $items       = $exercise->items->sortBy('sort_order')->values();
    $totalPoints = $items->sum('points');
    $itemCount   = $items->count();

    $diffColor = match($exercise->difficulty) {
        'easy'   => '#10b981',
        'medium' => '#f59e0b',
        'hard'   => '#ef4444',
        default  => '#6b7280',
    };

    $diffLabel = match($exercise->difficulty) {
        'easy'   => __('ui.quiz.difficulty_easy'),
        'medium' => __('ui.quiz.difficulty_medium'),
        'hard'   => __('ui.quiz.difficulty_hard'),
        default  => '',
    };

    $exerciseTypeLabel = match($exercise->exercise_type) {
        'interactive'       => 'Exercice interactif',
        'pdf'               => 'PDF',
        'audio_interactive' => 'Audio + questions',
        'video_interactive' => 'Vidéo + questions',
        default             => 'Exercice',
    };
  @endphp

  <div class="za-exPage">
    <div class="za-exBg"></div>

    <x-container class="py-10 relative z-10">

      {{-- Header --}}
      <div class="flex items-center justify-between gap-4 mb-8">
        <a href="javascript:history.back()" class="za-exBackBtn">{{ __('ui.quiz.back_btn') }}</a>

        <div class="za-exMeta">
          <span class="za-exMetaTag">{{ __('ui.quiz.exercise_badge') }}</span>

          <span class="za-exMetaTag">
            {{ $exerciseTypeLabel }}
          </span>

          @if($diffLabel)
            <span class="za-exMetaTag" style="background:{{ $diffColor }}22;color:{{ $diffColor }};">
              {{ $diffLabel }}
            </span>
          @endif

          @if($exercise->estimated_duration_minutes)
            <span class="za-exMetaTag">⏱ {{ $exercise->estimated_duration_minutes }} min</span>
          @endif

          <span class="za-exMetaTag">{{ $totalPoints }} {{ $totalPoints > 1 ? __('ui.quiz.pts') : __('ui.quiz.pt') }}</span>
        </div>
      </div>

      <h1 class="za-exTitle">{{ $title }}</h1>

      @if($description)
        <p class="za-exSubtitle">{{ $description }}</p>
      @endif

      @if($instructions)
        <div class="za-exInstructions">
          <span class="za-exInstructionsIcon">📋</span>
          <p>{{ $instructions }}</p>
        </div>
      @endif

      {{-- Exercise media: video / audio / pdf --}}
      @if($exercise->video_path || $exercise->audio_path || $exercise->pdf_path)
        <div class="za-exMediaSection">

          @if($exercise->video_path)
            <div class="za-exMediaCard">
              <div class="za-exMediaHeader">
                <div>
                  <h2>🎬 Regarde la vidéo avant de répondre</h2>
                  <p>Cette vidéo fait partie de l’exercice.</p>
                </div>
              </div>

              <video
                controls
                controlsList="nodownload"
                preload="metadata"
                class="za-exVideo"
              >
                <source src="{{ Storage::disk('public')->url($exercise->video_path) }}" type="video/mp4">
                Votre navigateur ne supporte pas la vidéo.
              </video>
            </div>
          @endif

          @if($exercise->audio_path)
            <div class="za-exMediaCard">
              <div class="za-exMediaHeader">
                <div>
                  <h2>🎧 Écoute l’audio avant de répondre</h2>
                  <p>Cet audio fait partie de l’exercice.</p>
                </div>
              </div>

              <audio
                controls
                controlsList="nodownload"
                preload="metadata"
                class="za-exAudio"
              >
                <source src="{{ Storage::disk('public')->url($exercise->audio_path) }}">
                Votre navigateur ne supporte pas l’audio.
              </audio>
            </div>
          @endif

          @if($exercise->pdf_path)
            <div class="za-exMediaCard">
              <div class="za-exMediaHeader">
                <div>
                  <h2>📄 Document de l’exercice</h2>
                  <p>Lis le document avant de répondre.</p>
                </div>
              </div>

              <div class="za-exPdfWrap">
                <embed
                  src="{{ Storage::disk('public')->url($exercise->pdf_path) }}#toolbar=1&navpanes=0&view=FitH"
                  type="application/pdf"
                  class="za-exPdf"
                />
              </div>
            </div>
          @endif

        </div>
      @endif

      {{-- Previous attempt notice --}}
      @if($lastAttempt)
        <div class="za-exPrevAttempt">
          <span>{{ __('ui.quiz.last_attempt') }} <strong>{{ $lastAttempt->score }}/{{ $lastAttempt->max_score }} {{ __('ui.quiz.pts') }}</strong>
            ({{ $lastAttempt->percentage }}%)</span>
          <a href="{{ route('exercise.result', $lastAttempt) }}" class="za-exPrevLink">{{ __('ui.quiz.see_results') }}</a>
        </div>
      @endif

      @if($itemCount === 0)
        <div class="za-exEmptyState">
          {{ __('ui.quiz.no_questions') }}
        </div>
      @else

        {{-- Progress bar --}}
        <div class="za-exProgressBar mt-6">
          <div class="za-exProgressFill" style="width:0%" id="za-progress"></div>
        </div>
        <p class="za-exProgressLabel" id="za-progressLabel">{{ __('ui.quiz.answered_progress', ['count' => 0, 'total' => $itemCount]) }}</p>

        <form
          method="POST"
          action="{{ route('exercise.submit', $exercise) }}"
          id="za-exForm"
          class="mt-6 space-y-6"
        >
          @csrf

          @foreach($items as $idx => $item)
            @php
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

              $typeColor = match($item->item_type) {
                'single_choice','multiple_choice','true_false' => '#6366f1',
                'matching','ordering'                          => '#f59e0b',
                'fill_blank','word_bank'                       => '#10b981',
                'categorization'                               => '#06b6d4',
                'short_answer'                                 => '#8b5cf6',
                'calculation'                                  => '#ef4444',
                default                                        => '#6b7280',
              };

              $answers = $item->answers->sortBy('sort_order')->values();
            @endphp

            <div class="za-exCard" data-item-id="{{ $item->id }}" data-answered="0">
              <div class="za-exCardTop">
                <div class="za-exCardNum">{{ __('ui.quiz.q_prefix') }}{{ $idx + 1 }}</div>
                <span class="za-exTypeTag" style="background:{{ $typeColor }}22;color:{{ $typeColor }};">{{ $typeLabel }}</span>
                <span class="za-exPoints">{{ $item->points }} {{ $item->points > 1 ? __('ui.quiz.pts') : __('ui.quiz.pt') }}</span>
              </div>

              {{-- Statement --}}
              @if($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" class="za-exItemImage" alt="" loading="lazy">
              @endif

              @if($item->item_type === 'fill_blank')
                @php
                  $blankParts = explode('___', $item->statement ?? '');
                  $blankCount = count($blankParts) - 1;
                @endphp

                <p class="za-exStatement za-exStatementFill">
                  @foreach($blankParts as $bIdx => $part)
                    {!! nl2br(e($part)) !!}

                    @if($bIdx < $blankCount)
                      <input
                        type="text"
                        name="answers[{{ $item->id }}][{{ $bIdx }}]"
                        class="za-exBlankInput"
                        placeholder="..."
                        autocomplete="off"
                        data-item="{{ $item->id }}"
                        onchange="zaMarkAnswered({{ $item->id }})"
                      >
                    @endif
                  @endforeach
                </p>
              @else
                <p class="za-exStatement">{!! nl2br(e($item->statement ?? '')) !!}</p>
              @endif

              {{-- single_choice / true_false --}}
              @if(in_array($item->item_type, ['single_choice', 'true_false']))
                <div class="za-exOptions">
                  @foreach($answers as $ans)
                    <label class="za-exOption">
                      <input
                        type="radio"
                        name="answers[{{ $item->id }}]"
                        value="{{ $ans->id }}"
                        class="za-exRadio"
                        onchange="zaMarkAnswered({{ $item->id }})"
                      >
                      <span class="za-exOptionText">
                        @if($ans->image_path)
                          <img src="{{ asset('storage/' . $ans->image_path) }}" class="za-exAnswerImg" alt="">
                        @endif
                        {{ $ans->answer_text }}
                      </span>
                    </label>
                  @endforeach
                </div>
              @endif

              {{-- multiple_choice --}}
              @if($item->item_type === 'multiple_choice')
                <p class="za-exHint">{{ __('ui.quiz.hint_multiple_ex') }}</p>
                <div class="za-exOptions">
                  @foreach($answers as $ans)
                    <label class="za-exOption">
                      <input
                        type="checkbox"
                        name="answers[{{ $item->id }}][]"
                        value="{{ $ans->id }}"
                        class="za-exCheckbox"
                        onchange="zaMarkAnswered({{ $item->id }})"
                      >
                      <span class="za-exOptionText">
                        @if($ans->image_path)
                          <img src="{{ asset('storage/' . $ans->image_path) }}" class="za-exAnswerImg" alt="">
                        @endif
                        {{ $ans->answer_text }}
                      </span>
                    </label>
                  @endforeach
                </div>
              @endif

              {{-- matching --}}
              @if($item->item_type === 'matching')
                @php $shuffledRight = $answers->pluck('match_text')->shuffle()->unique()->values(); @endphp
                <p class="za-exHint">{{ __('ui.quiz.hint_matching_ex') }}</p>

                <div class="za-exMatchGrid">
                  @foreach($answers as $ans)
                    <div class="za-exMatchRow">
                      <span class="za-exMatchLeft">{{ $ans->answer_text }}</span>
                      <span class="za-exMatchArrow">→</span>
                      <select
                        name="answers[{{ $item->id }}][{{ $ans->id }}]"
                        class="za-exSelect"
                        onchange="zaMarkAnswered({{ $item->id }})"
                      >
                        <option value="">{{ __('ui.quiz.choose_placeholder') }}</option>
                        @foreach($shuffledRight as $mt)
                          <option value="{{ $mt }}">{{ $mt }}</option>
                        @endforeach
                      </select>
                    </div>
                  @endforeach
                </div>
              @endif

              {{-- ordering --}}
              @if($item->item_type === 'ordering')
                @php $shuffledItems = $answers->shuffle()->values(); @endphp
                <p class="za-exHint">{{ __('ui.quiz.hint_ordering_ex') }}</p>

                <div class="za-exOrderGrid">
                  @foreach($shuffledItems as $ans)
                    <div class="za-exOrderRow">
                      <select
                        name="answers[{{ $item->id }}][{{ $ans->id }}]"
                        class="za-exSelectSmall"
                        onchange="zaMarkAnswered({{ $item->id }})"
                      >
                        <option value="">—</option>
                        @for($p = 1; $p <= $answers->count(); $p++)
                          <option value="{{ $p }}">{{ $p }}</option>
                        @endfor
                      </select>
                      <span class="za-exOrderText">{{ $ans->answer_text }}</span>
                    </div>
                  @endforeach
                </div>
              @endif

              {{-- word_bank --}}
              @if($item->item_type === 'word_bank')
                @php $shuffledWords = $answers->shuffle()->values(); @endphp
                <p class="za-exHint">{{ __('ui.quiz.hint_word_bank') }}</p>

                <div class="za-exWordBank" id="wb-{{ $item->id }}">
                  @foreach($shuffledWords as $ans)
                    <button
                      type="button"
                      class="za-exWordChip"
                      data-item="{{ $item->id }}"
                      data-answer="{{ $ans->id }}"
                      onclick="zaToggleWord(this)"
                    >{{ $ans->answer_text }}</button>

                    <input
                      type="checkbox"
                      name="answers[{{ $item->id }}][]"
                      value="{{ $ans->id }}"
                      id="wb-check-{{ $ans->id }}"
                      style="display:none"
                      onchange="zaMarkAnswered({{ $item->id }})"
                    >
                  @endforeach
                </div>
              @endif

              {{-- categorization --}}
              @if($item->item_type === 'categorization')
                @php $categories = $answers->pluck('category')->filter()->unique()->values(); @endphp
                <p class="za-exHint">{{ __('ui.quiz.hint_categorization') }}</p>

                <div class="za-exCategGrid">
                  @foreach($answers as $ans)
                    <div class="za-exCategRow">
                      <span class="za-exCategItem">{{ $ans->answer_text }}</span>
                      <select
                        name="answers[{{ $item->id }}][{{ $ans->id }}]"
                        class="za-exSelect"
                        onchange="zaMarkAnswered({{ $item->id }})"
                      >
                        <option value="">{{ __('ui.quiz.category_placeholder') }}</option>
                        @foreach($categories as $cat)
                          <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                      </select>
                    </div>
                  @endforeach
                </div>
              @endif

              {{-- short_answer --}}
              @if($item->item_type === 'short_answer')
                <input
                  type="text"
                  name="answers[{{ $item->id }}]"
                  class="za-exTextInput"
                  placeholder="{{ __('ui.quiz.your_answer_ph') }}"
                  autocomplete="off"
                  onchange="zaMarkAnswered({{ $item->id }})"
                  oninput="zaMarkAnswered({{ $item->id }})"
                >
              @endif

              {{-- calculation --}}
              @if($item->item_type === 'calculation')
                @php $calcAns = $answers->first(); @endphp

                <input
                  type="number"
                  name="answers[{{ $item->id }}]"
                  class="za-exNumberInput"
                  step="any"
                  placeholder="0"
                  onchange="zaMarkAnswered({{ $item->id }})"
                  oninput="zaMarkAnswered({{ $item->id }})"
                >

                @if($calcAns && (float)$calcAns->tolerance > 0)
                  <p class="za-exHint">{{ __('ui.quiz.tolerance', ['val' => $calcAns->tolerance]) }}</p>
                @endif
              @endif

              {{-- Hint --}}
              @if($item->hint)
                <details class="za-exHintDetails">
                  <summary>{{ __('ui.quiz.hint_label') }}</summary>
                  <p>{{ $item->hint }}</p>
                </details>
              @endif
            </div>
          @endforeach

          <div class="za-exSubmitArea">
            <button type="submit" id="za-submitBtn" class="za-exSubmitBtn">
              {{ __('ui.quiz.submit_btn') }}
            </button>

            <p class="za-exSubmitNote" id="za-answeredNote">
              {{ __('ui.quiz.answered_count', ['count' => 0, 'total' => $itemCount]) }}
            </p>
          </div>
        </form>
      @endif

    </x-container>
  </div>

  <style>
    .za-exPage {
      min-height: 100vh;
      position: relative;
      color: #f1f5f9;
    }

    .za-exBg {
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(900px 500px at 10% 5%, rgba(16,185,129,.14), transparent 60%),
        radial-gradient(700px 400px at 90% 90%, rgba(99,102,241,.12), transparent 55%),
        linear-gradient(160deg, #0b1a28 0%, #07111d 100%);
    }

    .za-exBackBtn {
      color: rgba(255,255,255,.6);
      font-size: .85rem;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 999px;
      padding: .45rem .9rem;
      transition: .15s;
    }

    .za-exBackBtn:hover {
      color: #fff;
      border-color: rgba(255,255,255,.25);
    }

    .za-exMeta {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      align-items: center;
    }

    .za-exMetaTag {
      font-size: .78rem;
      font-weight: 700;
      border-radius: 999px;
      padding: .3rem .75rem;
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.1);
      color: rgba(255,255,255,.8);
    }

    .za-exTitle {
      font-size: clamp(1.5rem, 4vw, 2.2rem);
      font-weight: 900;
      color: #fff;
      line-height: 1.2;
      margin-top: .5rem;
    }

    .za-exSubtitle {
      color: rgba(255,255,255,.65);
      font-size: .95rem;
      margin-top: .6rem;
      line-height: 1.65;
    }

    .za-exInstructions {
      margin-top: 1.2rem;
      display: flex;
      gap: .85rem;
      align-items: flex-start;
      border-radius: 16px;
      border: 1px solid rgba(234,179,8,.2);
      background: rgba(234,179,8,.06);
      padding: 1rem 1.1rem;
      color: rgba(255,255,255,.85);
      font-size: .9rem;
      line-height: 1.6;
    }

    .za-exInstructionsIcon {
      font-size: 1.2rem;
      flex-shrink: 0;
    }

    .za-exMediaSection {
      margin-top: 1.6rem;
      display: grid;
      gap: 1rem;
    }

    .za-exMediaCard {
      border-radius: 24px;
      border: 1px solid rgba(255,255,255,.09);
      background: rgba(255,255,255,.045);
      backdrop-filter: blur(8px);
      padding: 1rem;
      box-shadow: 0 18px 45px rgba(0,0,0,.24);
    }

    .za-exMediaHeader {
      margin-bottom: .85rem;
    }

    .za-exMediaHeader h2 {
      margin: 0;
      color: rgba(255,255,255,.95);
      font-size: 1.05rem;
      font-weight: 900;
    }

    .za-exMediaHeader p {
      margin: .25rem 0 0;
      color: rgba(255,255,255,.55);
      font-size: .85rem;
    }

    .za-exVideo {
      display: block;
      width: 100%;
      max-height: 560px;
      border-radius: 18px;
      background: #000;
      border: 1px solid rgba(255,255,255,.12);
      outline: none;
    }

    .za-exAudio {
      width: 100%;
      display: block;
    }

    .za-exPdfWrap {
      overflow: hidden;
      border-radius: 18px;
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(0,0,0,.25);
    }

    .za-exPdf {
      width: 100%;
      height: 650px;
      display: block;
    }

    .za-exPrevAttempt {
      margin-top: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      border-radius: 14px;
      border: 1px solid rgba(16,185,129,.2);
      background: rgba(16,185,129,.07);
      padding: .8rem 1.1rem;
      font-size: .88rem;
      color: rgba(255,255,255,.8);
    }

    .za-exPrevLink {
      color: #10b981;
      font-weight: 700;
      text-decoration: none;
      white-space: nowrap;
    }

    .za-exPrevLink:hover {
      text-decoration: underline;
    }

    .za-exProgressBar {
      height: 6px;
      border-radius: 999px;
      background: rgba(255,255,255,.08);
      overflow: hidden;
    }

    .za-exProgressFill {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, #10b981, #6366f1);
      transition: width .3s ease;
    }

    .za-exProgressLabel {
      font-size: .8rem;
      color: rgba(255,255,255,.45);
      margin-top: .4rem;
      text-align: right;
    }

    .za-exEmptyState {
      margin-top: 2rem;
      text-align: center;
      color: rgba(255,255,255,.4);
      font-size: .95rem;
      padding: 3rem;
      border: 1px dashed rgba(255,255,255,.1);
      border-radius: 20px;
    }

    .za-exCard {
      position: relative;
      border-radius: 22px;
      border: 1px solid rgba(255,255,255,.08);
      background: rgba(255,255,255,.04);
      backdrop-filter: blur(6px);
      padding: 1.6rem;
      transition: border-color .2s;
    }

    .za-exCard.is-answered {
      border-color: rgba(16,185,129,.3);
    }

    .za-exCardTop {
      display: flex;
      align-items: center;
      gap: .6rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    .za-exCardNum {
      font-weight: 900;
      font-size: .95rem;
      color: rgba(255,255,255,.5);
      min-width: 2rem;
    }

    .za-exTypeTag {
      font-size: .75rem;
      font-weight: 800;
      border-radius: 999px;
      padding: .25rem .7rem;
    }

    .za-exPoints {
      margin-left: auto;
      font-size: .8rem;
      font-weight: 700;
      color: rgba(255,255,255,.4);
    }

    .za-exStatement {
      color: rgba(255,255,255,.92);
      font-size: 1rem;
      line-height: 1.7;
      margin-bottom: 1.2rem;
    }

    .za-exStatementFill {
      display: inline;
    }

    .za-exItemImage {
      width: 100%;
      max-height: 220px;
      object-fit: contain;
      border-radius: 14px;
      margin-bottom: 1rem;
      border: 1px solid rgba(255,255,255,.08);
    }

    .za-exOptions {
      display: flex;
      flex-direction: column;
      gap: .6rem;
    }

    .za-exOption {
      display: flex;
      align-items: center;
      gap: .85rem;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.08);
      background: rgba(255,255,255,.03);
      padding: .75rem 1rem;
      cursor: pointer;
      transition: border-color .15s, background .15s;
    }

    .za-exOption:hover {
      border-color: rgba(255,255,255,.2);
      background: rgba(255,255,255,.06);
    }

    .za-exOption:has(input:checked) {
      border-color: rgba(99,102,241,.6);
      background: rgba(99,102,241,.1);
    }

    .za-exRadio,
    .za-exCheckbox {
      accent-color: #6366f1;
      width: 16px;
      height: 16px;
      flex-shrink: 0;
    }

    .za-exOptionText {
      color: rgba(255,255,255,.88);
      font-size: .93rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .za-exAnswerImg {
      max-width: 80px;
      max-height: 60px;
      border-radius: 8px;
      object-fit: contain;
    }

    .za-exHint {
      font-size: .8rem;
      color: rgba(255,255,255,.4);
      margin-bottom: .8rem;
      font-style: italic;
    }

    .za-exMatchGrid,
    .za-exOrderGrid,
    .za-exCategGrid {
      display: flex;
      flex-direction: column;
      gap: .7rem;
    }

    .za-exMatchRow {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      gap: .75rem;
    }

    .za-exMatchLeft,
    .za-exCategItem {
      color: rgba(255,255,255,.9);
      font-size: .92rem;
      background: rgba(255,255,255,.05);
      border-radius: 10px;
      padding: .55rem .85rem;
      border: 1px solid rgba(255,255,255,.08);
    }

    .za-exMatchArrow {
      color: rgba(255,255,255,.3);
      font-size: 1.1rem;
    }

    .za-exOrderRow,
    .za-exCategRow {
      display: flex;
      align-items: center;
      gap: .85rem;
    }

    .za-exOrderRow {
      background: rgba(255,255,255,.04);
      border-radius: 12px;
      padding: .7rem .9rem;
      border: 1px solid rgba(255,255,255,.07);
    }

    .za-exOrderText {
      color: rgba(255,255,255,.88);
      font-size: .92rem;
    }

    .za-exSelect,
    .za-exSelectSmall {
      background: rgba(255,255,255,.07);
      color: #fff;
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 10px;
      padding: .5rem .75rem;
      font-size: .88rem;
      outline: none;
      cursor: pointer;
      transition: border-color .15s;
    }

    .za-exSelect {
      flex: 1;
    }

    .za-exSelectSmall {
      width: 70px;
      flex-shrink: 0;
    }

    .za-exSelect:focus,
    .za-exSelectSmall:focus {
      border-color: rgba(99,102,241,.5);
    }

    .za-exSelect option,
    .za-exSelectSmall option {
      background: #0d1e30;
    }

    .za-exWordBank {
      display: flex;
      flex-wrap: wrap;
      gap: .55rem;
    }

    .za-exWordChip {
      border: 1px solid rgba(16,185,129,.35);
      background: rgba(16,185,129,.08);
      color: rgba(255,255,255,.85);
      border-radius: 999px;
      padding: .45rem .9rem;
      font-size: .88rem;
      font-weight: 600;
      cursor: pointer;
      transition: .15s;
    }

    .za-exWordChip:hover {
      background: rgba(16,185,129,.16);
    }

    .za-exWordChip.is-selected {
      background: rgba(16,185,129,.3);
      border-color: #10b981;
      color: #fff;
    }

    .za-exBlankInput {
      display: inline-block;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 8px;
      color: #fff;
      padding: .3rem .6rem;
      font-size: .9rem;
      width: 120px;
      outline: none;
      vertical-align: middle;
      margin: 0 .2rem;
      transition: border-color .15s;
    }

    .za-exBlankInput:focus {
      border-color: rgba(16,185,129,.5);
    }

    .za-exTextInput,
    .za-exNumberInput {
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 12px;
      color: #fff;
      padding: .75rem 1rem;
      font-size: .93rem;
      outline: none;
      transition: border-color .15s;
    }

    .za-exTextInput {
      width: 100%;
    }

    .za-exNumberInput {
      width: 180px;
      font-size: 1.1rem;
    }

    .za-exTextInput:focus {
      border-color: rgba(139,92,246,.5);
    }

    .za-exNumberInput:focus {
      border-color: rgba(239,68,68,.5);
    }

    .za-exHintDetails {
      margin-top: 1rem;
      border-radius: 12px;
      border: 1px solid rgba(234,179,8,.18);
      background: rgba(234,179,8,.05);
      padding: .7rem .95rem;
      font-size: .85rem;
      color: rgba(255,255,255,.7);
    }

    .za-exHintDetails summary {
      cursor: pointer;
      color: rgba(234,179,8,.85);
      font-weight: 700;
      list-style: none;
    }

    .za-exHintDetails p {
      margin-top: .5rem;
    }

    .za-exSubmitArea {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .75rem;
      padding: 1.5rem 0;
    }

    .za-exSubmitBtn {
      background: linear-gradient(135deg, #10b981, #059669);
      color: #fff;
      font-size: 1.05rem;
      font-weight: 900;
      border: none;
      border-radius: 16px;
      padding: 1rem 2.5rem;
      cursor: pointer;
      transition: .18s;
      box-shadow: 0 12px 30px rgba(16,185,129,.35);
    }

    .za-exSubmitBtn:hover {
      filter: brightness(1.07);
      transform: translateY(-1px);
    }

    .za-exSubmitBtn:disabled {
      opacity: .6;
      cursor: default;
      transform: none;
    }

    .za-exSubmitNote {
      font-size: .82rem;
      color: rgba(255,255,255,.4);
    }
  </style>

  <script>
    const za_total   = {{ $itemCount }};
    const za_answeredSet = new Set();

    function zaMarkAnswered(itemId) {
      const card = document.querySelector(`[data-item-id="${itemId}"]`);
      if (!card) return;

      const wasAnswered = card.dataset.answered === '1';
      let isNowAnswered = false;

      const radios  = card.querySelectorAll('input[type=radio]:checked, input[type=checkbox]:checked');
      const texts   = card.querySelectorAll('input[type=text], input[type=number]');
      const selects = card.querySelectorAll('select');

      if (radios.length > 0) isNowAnswered = true;
      texts.forEach(t => { if (t.value.trim()) isNowAnswered = true; });
      selects.forEach(s => { if (s.value) isNowAnswered = true; });

      if (isNowAnswered && !wasAnswered) {
        card.dataset.answered = '1';
        card.classList.add('is-answered');
        za_answeredSet.add(itemId);
      } else if (!isNowAnswered && wasAnswered) {
        card.dataset.answered = '0';
        card.classList.remove('is-answered');
        za_answeredSet.delete(itemId);
      }

      const count = za_answeredSet.size;
      const pct = za_total > 0 ? (count / za_total) * 100 : 0;

      const progress = document.getElementById('za-progress');
      if (progress) progress.style.width = pct + '%';

      const progressLabel = document.getElementById('za-progressLabel');
      if (progressLabel) {
        progressLabel.textContent = @json(__('ui.quiz.answered_progress'))
          .replace(':count', count)
          .replace(':total', za_total);
      }

      const noteEl = document.getElementById('za-answeredNote');
      if (noteEl) {
        noteEl.textContent = @json(__('ui.quiz.answered_count'))
          .replace(':count', count)
          .replace(':total', za_total);
      }
    }

    function zaToggleWord(btn) {
      const answerId = btn.dataset.answer;
      const checkbox = document.getElementById('wb-check-' + answerId);
      if (!checkbox) return;

      const isSelected = btn.classList.contains('is-selected');

      if (isSelected) {
        btn.classList.remove('is-selected');
        checkbox.checked = false;
      } else {
        btn.classList.add('is-selected');
        checkbox.checked = true;
      }

      checkbox.dispatchEvent(new Event('change'));
    }

    document.getElementById('za-exForm')?.addEventListener('submit', function () {
      const btn = document.getElementById('za-submitBtn');

      if (btn) {
        btn.disabled = true;
        btn.textContent = @json(__('ui.quiz.submitting'));
      }
    });
  </script>
@endsection