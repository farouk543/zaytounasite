@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div
    class="student-quiz-page"
    x-data="summerClubQuiz({
        questions: @js($questions),
        submitUrl: @js($submitUrl),
        csrfToken: @js(csrf_token())
    })"
>
    <section class="student-quiz-shell">
        <div class="za-container">
            <div class="student-quiz-panel">
                <div class="student-quiz-top">
                    <div>
                        <span class="summer-club-eyebrow">Quiz interactif</span>
                        <h1>{{ $quiz->title }}</h1>
                    </div>
                    <span class="student-quiz-counter" x-text="finished ? 'Résultat' : ((currentIndex + 1) + ' / ' + questions.length)"></span>
                </div>

                @if($lastAttempt)
                    <div class="student-quiz-badge">
                        Meilleur score : {{ $lastAttempt->score }} / {{ $lastAttempt->total }} points - {{ number_format((float) $lastAttempt->percentage, 0) }}%
                    </div>
                @endif

                <div class="student-quiz-progress" aria-hidden="true">
                    <span :style="`width: ${progress}%`"></span>
                </div>

                <template x-if="!finished && questions.length">
                    <div class="student-quiz-question" x-transition>
                        <template x-if="current.media_type">
                            <div class="student-learning-media">
                                <template x-if="current.media_type === 'image' && mediaSource(current)">
                                    <img :src="mediaSource(current)" alt="">
                                </template>
                                <template x-if="current.media_type === 'video' && current.media_path_url">
                                    <video :src="current.media_path_url" controls></video>
                                </template>
                                <template x-if="current.media_type === 'video' && !current.media_path_url && current.media_url">
                                    <a :href="current.media_url" target="_blank" rel="noopener">Ouvrir la vidéo</a>
                                </template>
                                <template x-if="current.media_type === 'audio' && mediaSource(current)">
                                    <audio :src="mediaSource(current)" controls></audio>
                                </template>
                            </div>
                        </template>

                        <h2 x-text="current.question"></h2>

                        <div class="student-quiz-options">
                            <template x-for="(label, key) in current.options" :key="key">
                                <button
                                    type="button"
                                    class="student-quiz-option"
                                    x-show="label"
                                    :class="{ 'is-selected': answers[current.id] === key }"
                                    @click="answers[current.id] = key"
                                >
                                    <span x-text="key.toUpperCase()"></span>
                                    <strong x-text="label"></strong>
                                </button>
                            </template>
                        </div>

                        <button type="button" class="student-quiz-next" :disabled="!answers[current.id] || submitting" @click="next()">
                            <span x-show="!submitting" x-text="currentIndex + 1 === questions.length ? 'Enregistrer mon résultat' : 'Question suivante'"></span>
                            <span x-show="submitting">Enregistrement...</span>
                        </button>
                    </div>
                </template>

                <template x-if="finished && result">
                    <div class="student-quiz-result" x-transition>
                        <div class="student-quiz-badge" x-text="result.passed ? 'Réussi' : 'À revoir'"></div>
                        <h2><span x-text="result.score"></span> / <span x-text="result.total"></span> points</h2>
                        <p><strong x-text="Math.round(result.percentage) + '%'"></strong> de réussite</p>
                        <p x-text="result.message"></p>
                    </div>
                </template>

                <template x-if="error">
                    <div class="student-quiz-correction">
                        <p x-text="error"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('summerClubQuiz', ({ questions, submitUrl, csrfToken }) => ({
        questions,
        submitUrl,
        csrfToken,
        currentIndex: 0,
        answers: {},
        startedAt: new Date().toISOString(),
        submitting: false,
        finished: false,
        result: null,
        error: '',
        get current() {
            return this.questions[this.currentIndex] || {};
        },
        get progress() {
            if (!this.questions.length) return 100;
            return this.finished ? 100 : Math.round((this.currentIndex / this.questions.length) * 100);
        },
        mediaSource(question) {
            return question.media_path_url || question.media_url || null;
        },
        next() {
            if (!this.answers[this.current.id] || this.submitting) return;

            if (this.currentIndex + 1 >= this.questions.length) {
                this.submit();
                return;
            }

            this.currentIndex++;
        },
        submit() {
            this.submitting = true;
            this.error = '';

            fetch(this.submitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    answers: this.answers,
                    started_at: this.startedAt
                })
            })
                .then((response) => response.json().then((data) => {
                    if (!response.ok) throw data;
                    return data;
                }))
                .then((data) => {
                    this.result = data;
                    this.finished = true;
                })
                .catch(() => {
                    this.error = "Impossible d'enregistrer la tentative. Veuillez réessayer.";
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    }));
});
</script>
@endpush
