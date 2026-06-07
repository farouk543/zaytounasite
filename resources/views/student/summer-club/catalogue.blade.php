@extends('layouts.app')

@section('title', 'Mon espace Club d ete')

@section('content')
<div class="student-club-page">
    <section class="student-club-hero">
        <div class="za-container">
            <span class="summer-club-eyebrow">Espace eleve</span>
            <h1>Mon Club d ete</h1>
            <p>Accede a tes formations, quiz interactifs et exercices debloques apres confirmation de ton abonnement.</p>
        </div>
    </section>

    <section class="student-club-section">
        <div class="za-container">
            <div class="student-club-homeGrid">
                <article class="student-club-featureCard student-club-featureCardPrimary">
                    <span class="student-club-type">Formations Club d ete</span>
                    <h2>Mes formations</h2>
                    <p>Retrouve les supports, fiches et activites organises par ressource pedagogique.</p>
                    <div class="student-club-statsRow">
                        <span>{{ $resources->count() }} formation(s)</span>
                        <span>{{ $resources->sum('published_quizzes_count') }} quiz</span>
                        <span>{{ $resources->sum('published_exercises_count') }} exercice(s)</span>
                    </div>
                    <a href="{{ route('student.club-ete.formations.index') }}" class="student-club-cardButton">Voir mes formations</a>
                </article>

                <article class="student-club-featureCard">
                    <span class="student-club-type">Quiz interactifs</span>
                    <h2>S entrainer en quiz</h2>
                    <p>Avance question par question, puis enregistre ton score officiel.</p>
                    <a href="#student-club-quizzes" class="student-club-cardButton">Voir les quiz</a>
                </article>

                <article class="student-club-featureCard">
                    <span class="student-club-type">Exercices interactifs</span>
                    <h2>Pratiquer</h2>
                    <p>Complete, relie, ordonne et sauvegarde tes resultats dans ton espace.</p>
                    <a href="#student-club-exercises" class="student-club-cardButton">Voir les exercices</a>
                </article>
            </div>

            @if($quizzes->isNotEmpty())
                <div class="student-club-subhead" id="student-club-quizzes">
                    <span class="summer-club-eyebrow">Progression</span>
                    <h2>Quiz interactifs</h2>
                </div>
                <div class="student-club-grid">
                    @foreach($quizzes as $quiz)
                        @php($attempt = $quizAttempts->get($quiz->id))
                        <a href="{{ route('student.club-ete.quiz.show', $quiz) }}" class="student-club-card student-club-cardLink">
                            <span class="student-club-type">Quiz</span>
                            <h2>{{ $quiz->title }}</h2>
                            @if($quiz->resource)
                                <p class="student-club-linked">Lie a : {{ $quiz->resource->title }}</p>
                            @endif
                            <p>{{ $quiz->questions_count }} question(s)</p>
                            <div class="student-club-metaLine">
                                @if($attempt)
                                    <span>Termine</span>
                                    <span>Meilleur score : {{ $attempt->score }}/{{ $attempt->total }} points ({{ number_format((float) $attempt->percentage, 0) }}%)</span>
                                    <span>{{ $attempt->passed ? 'Reussi' : 'A revoir' }}</span>
                                    @if($attempt->completed_at)
                                        <span>Derniere tentative : {{ $attempt->completed_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                @else
                                    <span>Non commence</span>
                                @endif
                            </div>
                            <span class="student-club-cardButton">{{ $attempt ? 'Revoir le quiz' : 'Commencer' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($exercises->isNotEmpty())
                <div class="student-club-subhead" id="student-club-exercises">
                    <span class="summer-club-eyebrow">Progression</span>
                    <h2>Exercices interactifs</h2>
                </div>
                <div class="student-club-grid">
                    @foreach($exercises as $exercise)
                        @php($attempt = $exerciseAttempts->get($exercise->id))
                        <a href="{{ route('student.club-ete.exercise.show', $exercise) }}" class="student-club-card student-club-cardLink">
                            <span class="student-club-type">Exercice</span>
                            <h2>{{ $exercise->title }}</h2>
                            @if($exercise->resource)
                                <p class="student-club-linked">Lie a : {{ $exercise->resource->title }}</p>
                            @endif
                            <p>{{ $exercise->items_count }} activite(s)</p>
                            <div class="student-club-metaLine">
                                @if($attempt)
                                    <span>Termine</span>
                                    <span>Meilleur score : {{ $attempt->score }}/{{ $attempt->total }} points ({{ number_format((float) $attempt->percentage, 0) }}%)</span>
                                    <span>{{ $attempt->passed ? 'Reussi' : 'A revoir' }}</span>
                                    @if($attempt->completed_at)
                                        <span>Derniere tentative : {{ $attempt->completed_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                @else
                                    <span>Non commence</span>
                                @endif
                            </div>
                            <span class="student-club-cardButton">{{ $attempt ? 'Refaire l exercice' : 'Faire l exercice' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
