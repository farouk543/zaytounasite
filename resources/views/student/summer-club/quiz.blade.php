@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div
    class="student-quiz-page"
    x-data="summerClubQuiz({
        questions: @js($questions)
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

                        <button type="button" class="student-quiz-next" :disabled="!answers[current.id]" @click="next()">
                            <span x-text="currentIndex + 1 === questions.length ? 'Voir mon résultat' : 'Question suivante'"></span>
                        </button>
                    </div>
                </template>

                <template x-if="finished">
                    <div class="student-quiz-result" x-transition>
                        <div class="student-quiz-badge" x-text="resultMessage"></div>
                        <h2><span x-text="score"></span> / <span x-text="totalPoints"></span> points</h2>
                        <p>
                            <strong x-text="correctCount"></strong> bonne(s) réponse(s) -
                            <strong x-text="percentage + '%'"></strong>
                        </p>

                        <div class="student-quiz-corrections">
                            <template x-for="(question, index) in questions" :key="question.id">
                                <article class="student-quiz-correction" :class="{ 'is-correct': answers[question.id] === question.correct }">
                                    <h3 x-text="(index + 1) + '. ' + question.question"></h3>
                                    <p>
                                        Ta réponse :
                                        <strong x-text="answers[question.id] ? answers[question.id].toUpperCase() : '-'"></strong>
                                        <span> / Bonne réponse : </span>
                                        <strong x-text="question.correct.toUpperCase()"></strong>
                                    </p>
                                    <p x-show="question.explanation" x-text="question.explanation"></p>
                                </article>
                            </template>
                        </div>
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
    Alpine.data('summerClubQuiz', ({ questions }) => ({
        questions,
        currentIndex: 0,
        answers: {},
        finished: false,
        get current() {
            return this.questions[this.currentIndex] || {};
        },
        get progress() {
            if (!this.questions.length) return 100;
            return this.finished ? 100 : Math.round((this.currentIndex / this.questions.length) * 100);
        },
        get totalPoints() {
            return this.questions.reduce((total, question) => total + Number(question.points || 0), 0);
        },
        get score() {
            return this.questions.reduce((total, question) => {
                return total + (this.answers[question.id] === question.correct ? Number(question.points || 0) : 0);
            }, 0);
        },
        get correctCount() {
            return this.questions.filter((question) => this.answers[question.id] === question.correct).length;
        },
        get percentage() {
            return this.totalPoints > 0 ? Math.round((this.score / this.totalPoints) * 100) : 0;
        },
        get resultMessage() {
            if (this.percentage >= 80) return 'Excellent travail';
            if (this.percentage >= 50) return 'Bonne progression';
            return 'Continue, tu vas y arriver';
        },
        mediaSource(question) {
            return question.media_path_url || question.media_url || null;
        },
        next() {
            if (!this.answers[this.current.id]) return;

            if (this.currentIndex + 1 >= this.questions.length) {
                this.finished = true;
                return;
            }

            this.currentIndex++;
        },
    }));
});
</script>
@endpush
