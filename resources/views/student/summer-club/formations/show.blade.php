@extends('layouts.app')

@section('title', $resource->title)

@section('content')
@php
    $coverUrl = $resource->cover_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($resource->cover_image_path)
        : null;

    $fileUrl = $resource->file_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($resource->file_path)
        : null;

    $typeLabel = match ($resource->type) {
        'quiz' => 'Quiz',
        'fiche' => 'Fiche de révision',
        'exercice' => 'Exercice',
        default => 'Formation',
    };
@endphp

<div class="student-club-page">
    <section class="student-formation-showHero">
        <div class="za-container">
            <a href="{{ route('student.club-ete.formations.index') }}" class="student-club-backLink">Retour aux formations</a>

            <div class="student-formation-showGrid">
                <div class="student-formation-showCopy">
                    <span class="student-club-type">{{ $typeLabel }}</span>
                    <h1>{{ $resource->title }}</h1>
                    <div class="student-club-metaLine">
                        @if($resource->subject)
                            <span>{{ $resource->subject }}</span>
                        @endif
                        @if($resource->level)
                            <span>{{ $resource->level }}</span>
                        @endif
                    </div>
                    @if($resource->description)
                        <p>{{ $resource->description }}</p>
                    @endif
                </div>

                <div class="student-formation-showMedia">
                    @if($coverUrl)
                        <img src="{{ $coverUrl }}" alt="{{ $resource->title }}">
                    @else
                        <div class="student-formation-fallback">{{ strtoupper(\Illuminate\Support\Str::substr($resource->title, 0, 1)) }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="student-club-section">
        <div class="za-container">
            @if($resource->content || $fileUrl)
                <div class="student-club-contentPanel">
                    <div class="student-club-subhead">
                        <span class="summer-club-eyebrow">Support</span>
                        <h2>Contenu pédagogique</h2>
                    </div>

                    @if($resource->content)
                        <div class="student-club-richContent">
                            {!! $resource->content !!}
                        </div>
                    @endif

                    @if($fileUrl)
                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="student-club-cardButton">Ouvrir le support</a>
                    @endif
                </div>
            @endif

            <div class="student-club-subhead">
                <span class="summer-club-eyebrow">Activités liées</span>
                <h2>Quiz et exercices</h2>
            </div>

            @if($resource->quizzes->isEmpty() && $resource->exercises->isEmpty())
                <div class="student-club-empty">
                    Les activités liées à cette formation seront ajoutées prochainement.
                </div>
            @endif

            @if($resource->quizzes->isNotEmpty())
                <div class="student-club-grid">
                    @foreach($resource->quizzes as $quiz)
                        <a href="{{ route('student.club-ete.quiz.show', $quiz) }}" class="student-club-card student-club-cardLink">
                            <span class="student-club-type">Quiz interactif</span>
                            <h2>{{ $quiz->title }}</h2>
                            @if($quiz->description)
                                <p>{{ \Illuminate\Support\Str::limit($quiz->description, 120) }}</p>
                            @endif
                            <div class="student-club-metaLine">
                                @if($quiz->subject)
                                    <span>{{ $quiz->subject }}</span>
                                @endif
                                @if($quiz->level)
                                    <span>{{ $quiz->level }}</span>
                                @endif
                                <span>{{ $quiz->questions_count }} question(s)</span>
                            </div>
                            <span class="student-club-cardButton">Commencer le quiz</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($resource->exercises->isNotEmpty())
                <div class="student-club-grid student-club-gridOffset">
                    @foreach($resource->exercises as $exercise)
                        @php
                            $exerciseCoverUrl = $exercise->cover_image_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($exercise->cover_image_path)
                                : $coverUrl;
                        @endphp
                        <a href="{{ route('student.club-ete.exercise.show', $exercise) }}" class="student-club-card student-club-cardLink student-club-cardWithMedia">
                            @if($exerciseCoverUrl)
                                <img src="{{ $exerciseCoverUrl }}" alt="{{ $exercise->title }}" loading="lazy">
                            @endif
                            <span class="student-club-type">Exercice interactif</span>
                            <h2>{{ $exercise->title }}</h2>
                            @if($exercise->description)
                                <p>{{ \Illuminate\Support\Str::limit($exercise->description, 120) }}</p>
                            @endif
                            <div class="student-club-metaLine">
                                @if($exercise->subject)
                                    <span>{{ $exercise->subject }}</span>
                                @endif
                                @if($exercise->level)
                                    <span>{{ $exercise->level }}</span>
                                @endif
                                <span>{{ $exercise->items_count }} activité(s)</span>
                            </div>
                            <span class="student-club-cardButton">Faire l’exercice</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
