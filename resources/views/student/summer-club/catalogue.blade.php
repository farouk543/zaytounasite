@extends('layouts.app')

@section('title', 'Mon espace Club d’été')

@section('content')
<div class="student-club-page">
    <section class="student-club-hero">
        <div class="za-container">
            <span class="summer-club-eyebrow">Espace élève</span>
            <h1>Mon Club d’été</h1>
            <p>Accède à tes formations, quiz interactifs et exercices débloqués après confirmation de ton abonnement.</p>
        </div>
    </section>

    <section class="student-club-section">
        <div class="za-container">
            <div class="student-club-homeGrid">
                <article class="student-club-featureCard student-club-featureCardPrimary">
                    <span class="student-club-type">Formations Club d’été</span>
                    <h2>Mes formations</h2>
                    <p>Retrouve les supports, fiches et activités organisés par ressource pédagogique.</p>
                    <div class="student-club-statsRow">
                        <span>{{ $resources->count() }} formation(s)</span>
                        <span>{{ $resources->sum('published_quizzes_count') }} quiz</span>
                        <span>{{ $resources->sum('published_exercises_count') }} exercice(s)</span>
                    </div>
                    <a href="{{ route('student.club-ete.formations.index') }}" class="student-club-cardButton">Voir mes formations</a>
                </article>

                <article class="student-club-featureCard">
                    <span class="student-club-type">Quiz interactifs</span>
                    <h2>S’entraîner en quiz</h2>
                    <p>Avance question par question, puis consulte ton score et la correction.</p>
                    <a href="#student-club-quizzes" class="student-club-cardButton">Voir les quiz</a>
                </article>

                <article class="student-club-featureCard">
                    <span class="student-club-type">Exercices interactifs</span>
                    <h2>Pratiquer</h2>
                    <p>Complète, relie, ordonne et corrige tes réponses directement dans ton espace.</p>
                    <a href="#student-club-exercises" class="student-club-cardButton">Voir les exercices</a>
                </article>
            </div>

            @if($quizzes->isNotEmpty())
                <div class="student-club-subhead" id="student-club-quizzes">
                    <span class="summer-club-eyebrow">Derniers contenus</span>
                    <h2>Quiz interactifs</h2>
                </div>
                <div class="student-club-grid">
                    @foreach($quizzes as $quiz)
                        <a href="{{ route('student.club-ete.quiz.show', $quiz) }}" class="student-club-card student-club-cardLink">
                            <span class="student-club-type">Quiz</span>
                            <h2>{{ $quiz->title }}</h2>
                            @if($quiz->resource)
                                <p class="student-club-linked">Lié à : {{ $quiz->resource->title }}</p>
                            @endif
                            <p>{{ $quiz->questions_count }} question(s)</p>
                            <span class="student-club-cardButton">Commencer</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($exercises->isNotEmpty())
                <div class="student-club-subhead" id="student-club-exercises">
                    <span class="summer-club-eyebrow">Activités</span>
                    <h2>Exercices interactifs</h2>
                </div>
                <div class="student-club-grid">
                    @foreach($exercises as $exercise)
                        <a href="{{ route('student.club-ete.exercise.show', $exercise) }}" class="student-club-card student-club-cardLink">
                            <span class="student-club-type">Exercice</span>
                            <h2>{{ $exercise->title }}</h2>
                            @if($exercise->resource)
                                <p class="student-club-linked">Lié à : {{ $exercise->resource->title }}</p>
                            @endif
                            <p>{{ $exercise->items_count }} activité(s)</p>
                            <span class="student-club-cardButton">Faire l’exercice</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
