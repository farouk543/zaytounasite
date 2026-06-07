@extends('layouts.app')

@section('title', $exercise->title)

@section('content')
<div
    class="student-quiz-page"
    x-data="summerClubExercise({
        items: @js($items),
        submitUrl: @js($submitUrl),
        csrfToken: @js(csrf_token())
    })"
>
    <section class="student-quiz-shell">
        <div class="za-container">
            <div class="student-quiz-panel">
                <div class="student-quiz-top">
                    <div>
                        <span class="summer-club-eyebrow">Exercice interactif</span>
                        <h1>{{ $exercise->title }}</h1>
                    </div>
                    <span class="student-quiz-counter" x-text="finished ? 'Resultat' : ((currentIndex + 1) + ' / ' + items.length)"></span>
                </div>

                @if($lastAttempt)
                    <div class="student-quiz-badge">
                        Meilleur score : {{ $lastAttempt->score }} / {{ $lastAttempt->total }} points - {{ number_format((float) $lastAttempt->percentage, 0) }}%
                    </div>
                @endif

                <div class="student-quiz-progress" aria-hidden="true">
                    <span :style="`width: ${progress}%`"></span>
                </div>

                <template x-if="!finished && items.length">
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
                                    <a :href="current.media_url" target="_blank" rel="noopener">Ouvrir la video</a>
                                </template>
                                <template x-if="current.media_type === 'audio' && mediaSource(current)">
                                    <audio :src="mediaSource(current)" controls></audio>
                                </template>
                            </div>
                        </template>

                        <p class="student-exercise-instruction" x-text="current.instruction"></p>
                        <h2 x-show="current.question" x-text="current.question"></h2>

                        <template x-if="current.type === 'multiple_choice'">
                            <div class="student-quiz-options">
                                <template x-for="option in optionArray(current)" :key="option.key">
                                    <label class="student-quiz-option">
                                        <input type="checkbox" :value="option.key" @change="toggleChoice(current, option.key, $event.target.checked)">
                                        <strong x-text="option.text"></strong>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="current.type === 'true_false'">
                            <div class="student-quiz-options student-quiz-optionsTwo">
                                <button type="button" class="student-quiz-option" :class="{ 'is-selected': answers[current.id] === 'true' }" @click="answers[current.id] = 'true'"><strong>Vrai</strong></button>
                                <button type="button" class="student-quiz-option" :class="{ 'is-selected': answers[current.id] === 'false' }" @click="answers[current.id] = 'false'"><strong>Faux</strong></button>
                            </div>
                        </template>

                        <template x-if="['fill_blank', 'short_answer'].includes(current.type)">
                            <input class="student-exercise-input" type="text" x-model="answers[current.id]" placeholder="Ta reponse">
                        </template>

                        <template x-if="current.type === 'matching'">
                            <div class="student-matching-grid">
                                <template x-for="left in (current.options?.left || [])" :key="left.key">
                                    <label>
                                        <span x-text="left.text"></span>
                                        <select x-model="matchingAnswers[current.id][left.key]">
                                            <option value="">Choisir</option>
                                            <template x-for="right in (shuffledRight[current.id] || [])" :key="right.key">
                                                <option :value="right.key" x-text="right.text"></option>
                                            </template>
                                        </select>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="current.type === 'ordering'">
                            <div class="student-ordering-list">
                                <template x-for="(option, index) in orderingAnswers[current.id]" :key="option.key">
                                    <div>
                                        <span x-text="option.text"></span>
                                        <button type="button" @click="moveOrder(current.id, index, -1)">Monter</button>
                                        <button type="button" @click="moveOrder(current.id, index, 1)">Descendre</button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="current.type === 'drag_drop'">
                            <div class="student-matching-grid">
                                <template x-for="item in (current.options?.items || [])" :key="item.key">
                                    <label>
                                        <span x-text="item.text"></span>
                                        <select x-model="dragAnswers[current.id][item.key]">
                                            <option value="">Choisir une zone</option>
                                            <template x-for="zone in (current.options?.zones || [])" :key="zone.key">
                                                <option :value="zone.key" x-text="zone.text"></option>
                                            </template>
                                        </select>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="current.type === 'image_labeling'">
                            <div class="student-matching-grid">
                                <template x-for="label in (current.options?.labels || [])" :key="label.key">
                                    <label>
                                        <span x-text="label.text"></span>
                                        <input type="text" x-model="labelAnswers[current.id][label.key]" placeholder="Reponse">
                                    </label>
                                </template>
                            </div>
                        </template>

                        <button type="button" class="student-quiz-next" :disabled="!hasAnswer(current) || submitting" @click="next()">
                            <span x-show="!submitting" x-text="currentIndex + 1 === items.length ? 'Enregistrer mon resultat' : 'Activite suivante'"></span>
                            <span x-show="submitting">Enregistrement...</span>
                        </button>
                    </div>
                </template>

                <template x-if="finished && result">
                    <div class="student-quiz-result" x-transition>
                        <div class="student-quiz-badge" x-text="result.passed ? 'Reussi' : 'A revoir'"></div>
                        <h2><span x-text="result.score"></span> / <span x-text="result.total"></span> points</h2>
                        <p><strong x-text="Math.round(result.percentage) + '%'"></strong> de reussite</p>
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
    Alpine.data('summerClubExercise', ({ items, submitUrl, csrfToken }) => ({
        items,
        submitUrl,
        csrfToken,
        currentIndex: 0,
        answers: {},
        matchingAnswers: {},
        orderingAnswers: {},
        dragAnswers: {},
        labelAnswers: {},
        shuffledRight: {},
        startedAt: new Date().toISOString(),
        submitting: false,
        finished: false,
        result: null,
        error: '',
        init() {
            this.items.forEach((item) => {
                this.matchingAnswers[item.id] = {};
                this.dragAnswers[item.id] = {};
                this.labelAnswers[item.id] = {};
                this.shuffledRight[item.id] = this.shuffle([...(item.options?.right || [])]);

                if (item.type === 'ordering') {
                    this.orderingAnswers[item.id] = this.shuffle([...this.optionArray(item)]);
                }
            });
        },
        get current() {
            return this.items[this.currentIndex] || {};
        },
        get progress() {
            if (!this.items.length) return 100;
            return this.finished ? 100 : Math.round((this.currentIndex / this.items.length) * 100);
        },
        mediaSource(item) {
            return item.media_path_url || item.media_url || null;
        },
        optionArray(item) {
            return Array.isArray(item.options) ? item.options : [];
        },
        shuffle(list) {
            return list
                .map((value) => ({ value, sort: Math.random() }))
                .sort((a, b) => a.sort - b.sort)
                .map(({ value }) => value);
        },
        toggleChoice(item, key, checked) {
            const current = Array.isArray(this.answers[item.id]) ? [...this.answers[item.id]] : [];
            this.answers[item.id] = checked ? [...new Set([...current, key])] : current.filter((value) => value !== key);
        },
        moveOrder(itemId, index, direction) {
            const next = index + direction;
            const list = this.orderingAnswers[itemId] || [];

            if (next < 0 || next >= list.length) return;

            [list[index], list[next]] = [list[next], list[index]];
            this.orderingAnswers[itemId] = [...list];
        },
        hasAnswer(item) {
            if (item.type === 'matching') return Object.values(this.matchingAnswers[item.id] || {}).some(Boolean);
            if (item.type === 'ordering') return (this.orderingAnswers[item.id] || []).length > 0;
            if (item.type === 'drag_drop') return Object.values(this.dragAnswers[item.id] || {}).some(Boolean);
            if (item.type === 'image_labeling') return Object.values(this.labelAnswers[item.id] || {}).some(Boolean);
            if (item.type === 'multiple_choice') return Array.isArray(this.answers[item.id]) && this.answers[item.id].length > 0;

            return Boolean(this.answers[item.id]);
        },
        answerPayload() {
            return this.items.reduce((payload, item) => {
                if (item.type === 'matching') {
                    payload[item.id] = this.matchingAnswers[item.id] || {};
                } else if (item.type === 'ordering') {
                    payload[item.id] = (this.orderingAnswers[item.id] || []).map((option) => option.key);
                } else if (item.type === 'drag_drop') {
                    payload[item.id] = this.dragAnswers[item.id] || {};
                } else if (item.type === 'image_labeling') {
                    payload[item.id] = this.labelAnswers[item.id] || {};
                } else {
                    payload[item.id] = this.answers[item.id] ?? null;
                }

                return payload;
            }, {});
        },
        next() {
            if (!this.hasAnswer(this.current) || this.submitting) return;

            if (this.currentIndex + 1 >= this.items.length) {
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
                    answers: this.answerPayload(),
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
                    this.error = "Impossible d'enregistrer la tentative. Veuillez reessayer.";
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    }));
});
</script>
@endpush
