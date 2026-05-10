@extends('layouts.app')

@section('content')
  @php
    $title       = $exercise->title_display ?? $exercise->title;
    $description = $exercise->description ?? null;
    $instructions= $exercise->instructions ?? null;
    $pdfUrl      = \Illuminate\Support\Facades\Storage::disk('public')->url($exercise->pdf_path);

    $diffColor = match($exercise->difficulty) {
        'easy'   => '#10b981',
        'medium' => '#f59e0b',
        'hard'   => '#ef4444',
        default  => '#6b7280',
    };
    $diffLabel = match($exercise->difficulty) {
        'easy'   => 'Facile',
        'medium' => 'Moyen',
        'hard'   => 'Difficile',
        default  => '',
    };
  @endphp

  <div class="za-exPage">
    <div class="za-exBg"></div>

    <x-container class="py-10 relative z-10">

      {{-- Header --}}
      <div class="flex items-center justify-between gap-4 mb-8">
        <a href="javascript:history.back()" class="za-exBackBtn">← Retour</a>

        <div class="za-exMeta">
          <span class="za-exMetaTag">📄 Exercice PDF</span>
          @if($diffLabel)
            <span class="za-exMetaTag" style="background:{{ $diffColor }}22;color:{{ $diffColor }};">{{ $diffLabel }}</span>
          @endif
          @if($exercise->estimated_duration_minutes)
            <span class="za-exMetaTag">⏱ {{ $exercise->estimated_duration_minutes }} min</span>
          @endif
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

      {{-- PDF Viewer --}}
      <div class="za-pdfContainer mt-8">
        <div class="za-pdfToolbar">
          <span style="color:rgba(255,255,255,.6);font-size:.85rem;">📄 {{ $title }}</span>
          <a href="{{ $pdfUrl }}" download class="za-pdfDownload">
            ⬇️ Télécharger le PDF
          </a>
        </div>

        {{-- Embedded PDF viewer --}}
        <div class="za-pdfEmbed">
          <embed
            src="{{ $pdfUrl }}#toolbar=1&navpanes=0&view=FitH"
            type="application/pdf"
            class="za-pdfFrame"
            title="{{ $title }}"
          >
          {{-- Fallback if embed not supported --}}
          <noembed>
            <div class="za-pdfFallback">
              <p>Votre navigateur ne supporte pas la prévisualisation PDF.</p>
              <a href="{{ $pdfUrl }}" target="_blank" class="za-exSubmitBtn" style="text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;margin-top:1rem;">
                📄 Ouvrir le PDF
              </a>
            </div>
          </noembed>
        </div>
      </div>

      {{-- Bottom action --}}
      <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap;padding:2rem 0;">
        <a href="{{ $pdfUrl }}" target="_blank" class="za-exSubmitBtn" style="text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;">
          📄 Ouvrir en plein écran
        </a>
        <a href="{{ $pdfUrl }}" download class="za-exBackBtn" style="padding:.9rem 1.5rem;display:inline-flex;align-items:center;gap:.5rem;">
          ⬇️ Télécharger
        </a>
      </div>

    </x-container>
  </div>

  <style>
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
    .za-exMeta { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
    .za-exMetaTag {
      font-size: .78rem; font-weight: 700; border-radius: 999px;
      padding: .3rem .75rem; background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.8);
    }
    .za-exTitle {
      font-size: clamp(1.5rem, 4vw, 2.2rem); font-weight: 900;
      color: #fff; line-height: 1.2; margin-top: .5rem;
    }
    .za-exSubtitle { color: rgba(255,255,255,.65); font-size: .95rem; margin-top: .6rem; line-height: 1.65; }
    .za-exInstructions {
      margin-top: 1.2rem; display: flex; gap: .85rem; align-items: flex-start;
      border-radius: 16px; border: 1px solid rgba(234,179,8,.2);
      background: rgba(234,179,8,.06); padding: 1rem 1.1rem;
      color: rgba(255,255,255,.85); font-size: .9rem; line-height: 1.6;
    }
    .za-exInstructionsIcon { font-size: 1.2rem; flex-shrink: 0; }
    .za-exSubmitBtn {
      background: linear-gradient(135deg, #10b981, #059669);
      color: #fff; font-size: 1rem; font-weight: 900;
      border: none; border-radius: 16px; padding: .9rem 2rem;
      cursor: pointer; transition: .18s;
      box-shadow: 0 10px 28px rgba(16,185,129,.3);
    }
    .za-exSubmitBtn:hover { filter: brightness(1.07); transform: translateY(-1px); }

    /* ── PDF Container ───────────────────────────────────── */
    .za-pdfContainer {
      border-radius: 22px;
      border: 1px solid rgba(255,255,255,.1);
      background: rgba(255,255,255,.03);
      backdrop-filter: blur(6px);
      overflow: hidden;
    }
    .za-pdfToolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: .85rem 1.2rem;
      border-bottom: 1px solid rgba(255,255,255,.07);
      background: rgba(255,255,255,.02);
    }
    .za-pdfDownload {
      font-size: .82rem;
      font-weight: 700;
      color: #10b981;
      text-decoration: none;
      border: 1px solid rgba(16,185,129,.3);
      border-radius: 999px;
      padding: .3rem .85rem;
      transition: .15s;
    }
    .za-pdfDownload:hover { background: rgba(16,185,129,.1); }
    .za-pdfEmbed { width: 100%; }
    .za-pdfFrame {
      display: block;
      width: 100%;
      height: 75vh;
      min-height: 500px;
      border: none;
    }
    .za-pdfFallback {
      padding: 3rem;
      text-align: center;
      color: rgba(255,255,255,.5);
    }
  </style>
@endsection
