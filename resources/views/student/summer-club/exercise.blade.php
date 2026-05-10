@extends('layouts.app')

@section('title', $exercise->title)

@section('content')
<div
    class="student-quiz-page"
    x-data="summerClubExercise({ items: @js($items) })"
>
    <section class="student-quiz-shell">
        <div class="za-container">
            <div class="student-quiz-panel">
                <div class="student-quiz-top">
                    <div>
                        <span class="summer-club-eyebrow">Exercice interactif</span>
                        <h1>{{ $exercise->title }}</h1>
                    </div>
                    <span class="student-quiz-counter" x-text="finished ? 'Correction' : ((currentIndex + 1) + ' / ' + items.length)"></span>
                </div>

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
                                    <a :href="current.media_url" target="_blank" rel="noopener">Ouvrir la vidéo</a>
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
                            <input class="student-exercise-input" type="text" x-model="answers[current.id]" placeholder="Ta réponse">
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
                                        <input type="text" x-model="labelAnswers[current.id][label.key]" placeholder="Réponse">
                                    </label>
                                </template>
                            </div>
                        </template>

                        <button type="button" class="student-quiz-next" :disabled="!hasAnswer(current)" @click="next()">
                            <span x-text="currentIndex + 1 === items.length ? 'Voir la correction' : 'Activité suivante'"></span>
                        </button>
                    </div>
                </template>

                <template x-if="finished">
                    <div class="student-quiz-result" x-transition>
                        <div class="student-quiz-badge" x-text="resultMessage"></div>
                        <h2><span x-text="score"></span> / <span x-text="totalPoints"></span> points</h2>
                        <p><strong x-text="percentage + '%'"></strong> de réussite</p>

                        <div class="student-quiz-corrections">
                            <template x-for="(item, index) in items" :key="item.id">
                                <article class="student-quiz-correction" :class="{ 'is-correct': isCorrect(item) }">
                                    <h3 x-text="(index + 1) + '. ' + item.instruction"></h3>
                                    <p x-show="item.question" x-text="item.question"></p>
                                    <p x-text="isCorrect(item) ? 'Réponse correcte' : 'Réponse à revoir'"></p>
                                    <p class="student-correction-detail" x-text="correctionText(item)"></p>
                                    <p x-show="item.explanation" x-text="item.explanation"></p>
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
    Alpine.data('summerClubExercise', ({ items }) => ({
        items,
        currentIndex: 0,
        answers: {},
        matchingAnswers: {},
        orderingAnswers: {},
        dragAnswers: {},
        labelAnswers: {},
        shuffledRight: {},
        finished: false,
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
        get totalPoints() {
            return this.items.reduce((total, item) => total + Number(item.points || 0), 0);
        },
        get score() {
            return this.items.reduce((total, item) => total + (this.isCorrect(item) ? Number(item.points || 0) : 0), 0);
        },
        get percentage() {
            return this.totalPoints > 0 ? Math.round((this.score / this.totalPoints) * 100) : 0;
        },
        get resultMessage() {
            if (this.percentage >= 80) return 'Bravo, maîtrise solide';
            if (this.percentage >= 50) return 'Bonne progression';
            return 'On révise et on recommence';
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
        normalize(value) {
            return String(value ?? '').trim().toLowerCase();
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
        isCorrect(item) {
            const correct = item.correct_answer || {};

            if (item.type === 'multiple_choice') {
                const expected = [...(correct.answers || [])].sort().join('|');
                const given = [...(this.answers[item.id] || [])].sort().join('|');
                return expected === given;
            }

            if (item.type === 'true_false') {
                return this.answers[item.id] === correct.answer;
            }

            if (['fill_blank', 'short_answer'].includes(item.type)) {
                return (correct.answers || []).map(this.normalize).includes(this.normalize(this.answers[item.id]));
            }

            if (item.type === 'matching') {
                return (correct.pairs || []).every((pair) => (this.matchingAnswers[item.id] || {})[pair.left] === pair.right);
            }

            if (item.type === 'ordering') {
                const given = (this.orderingAnswers[item.id] || []).map((option) => option.key).join('|');
                return (correct.order || []).join('|') === given;
            }

            if (item.type === 'drag_drop') {
                return (correct.matches || []).every((match) => (this.dragAnswers[item.id] || {})[match.item] === match.zone);
            }

            if (item.type === 'image_labeling') {
                return (correct.answers || []).every((answer) => this.normalize((this.labelAnswers[item.id] || {})[answer.label]) === this.normalize(answer.answer));
            }

            return false;
        },
        correctionText(item) {
            const correct = item.correct_answer || {};

            if (item.type === 'multiple_choice') {
                const labels = this.optionArray(item)
                    .filter((option) => (correct.answers || []).includes(option.key))
                    .map((option) => option.text);
                return labels.length ? `Réponse attendue : ${labels.join(', ')}` : '';
            }

            if (item.type === 'true_false') {
                return `Réponse attendue : ${correct.answer === 'true' ? 'Vrai' : 'Faux'}`;
            }

            if (['fill_blank', 'short_answer'].includes(item.type)) {
                return `Réponses acceptées : ${(correct.answers || []).join(', ')}`;
            }

            if (item.type === 'matching') {
                const left = item.options?.left || [];
                const right = item.options?.right || [];
                const pairs = (correct.pairs || []).map((pair) => {
                    const l = left.find((entry) => entry.key === pair.left)?.text || pair.left;
                    const r = right.find((entry) => entry.key === pair.right)?.text || pair.right;
                    return `${l} → ${r}`;
                });
                return pairs.length ? `Correspondances : ${pairs.join(' | ')}` : '';
            }

            if (item.type === 'ordering') {
                const ordered = (correct.order || []).map((key) => this.optionArray(item).find((entry) => entry.key === key)?.text || key);
                return ordered.length ? `Ordre attendu : ${ordered.join(' → ')}` : '';
            }

            if (item.type === 'drag_drop') {
                const items = item.options?.items || [];
                const zones = item.options?.zones || [];
                const matches = (correct.matches || []).map((match) => {
                    const itemText = items.find((entry) => entry.key === match.item)?.text || match.item;
                    const zoneText = zones.find((entry) => entry.key === match.zone)?.text || match.zone;
                    return `${itemText} → ${zoneText}`;
                });
                return matches.length ? `Zones attendues : ${matches.join(' | ')}` : '';
            }

            if (item.type === 'image_labeling') {
                const labels = item.options?.labels || [];
                const answers = (correct.answers || []).map((answer) => {
                    const label = labels.find((entry) => entry.key === answer.label)?.text || answer.label;
                    return `${label} : ${answer.answer}`;
                });
                return answers.length ? `Légendes attendues : ${answers.join(' | ')}` : '';
            }

            return '';
        },
        next() {
            if (!this.hasAnswer(this.current)) return;
            if (this.currentIndex + 1 >= this.items.length) {
                this.finished = true;
                return;
            }
            this.currentIndex++;
        },
    }));
});
</script>
@endpush
