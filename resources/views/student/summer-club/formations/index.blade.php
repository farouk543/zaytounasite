@extends('layouts.app')

@section('title', 'Mes formations Club d’été')

@section('content')
<div class="student-club-page">
    <section class="student-club-hero">
        <div class="za-container">
            <span class="summer-club-eyebrow">Club d’été</span>
            <h1>Mes formations Club d’été</h1>
            <p>Explore les supports publiés, puis lance les quiz et exercices liés à chaque formation.</p>
        </div>
    </section>

    <section class="student-club-section">
        <div class="za-container">
            <div class="student-formation-grid">
                @forelse($resources as $resource)
                    @php
                        $coverUrl = $resource->cover_image_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($resource->cover_image_path)
                            : null;

                        $typeLabel = match ($resource->type) {
                            'quiz' => 'Quiz',
                            'fiche' => 'Fiche de révision',
                            'exercice' => 'Exercice',
                            default => 'Formation',
                        };
                    @endphp

                    <article class="student-formation-card">
                        <div class="student-formation-media">
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $resource->title }}" loading="lazy">
                            @else
                                <div class="student-formation-fallback">{{ strtoupper(\Illuminate\Support\Str::substr($resource->title, 0, 1)) }}</div>
                            @endif
                            <span>{{ $typeLabel }}</span>
                        </div>

                        <div class="student-formation-body">
                            <div class="student-club-metaLine">
                                @if($resource->subject)
                                    <span>{{ $resource->subject }}</span>
                                @endif
                                @if($resource->level)
                                    <span>{{ $resource->level }}</span>
                                @endif
                            </div>

                            <h2>{{ $resource->title }}</h2>

                            @if($resource->description)
                                <p>{{ \Illuminate\Support\Str::limit($resource->description, 135) }}</p>
                            @endif

                            <div class="student-club-statsRow">
                                @if($resource->published_quizzes_count > 0)
                                    <span>Quiz disponibles</span>
                                @endif
                                @if($resource->published_exercises_count > 0)
                                    <span>Exercices disponibles</span>
                                @endif
                            </div>

                            <a href="{{ route('student.club-ete.formations.show', $resource) }}" class="student-club-cardButton">Voir la formation</a>
                        </div>
                    </article>
                @empty
                    <div class="student-club-empty">
                        Les formations Club d’été seront ajoutées prochainement.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
