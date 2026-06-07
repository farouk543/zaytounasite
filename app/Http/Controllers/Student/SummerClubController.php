<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SummerClubEnrollment;
use App\Models\SummerClubExercise;
use App\Models\SummerClubExerciseAttempt;
use App\Models\SummerClubQuiz;
use App\Models\SummerClubQuizAttempt;
use App\Models\SummerClubResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SummerClubController extends Controller
{
    public function catalogue(Request $request)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        if ($this->enrollmentSubjects($enrollment) === []) {
            return view('student.summer-club.locked', [
                'message' => 'Votre accès Club d’été est actif, mais aucune matière n’est encore associée. Veuillez contacter l’administration.',
            ]);
        }

        $resources = SummerClubResource::query()
            ->withCount([
                'quizzes as published_quizzes_count' => fn ($query) => $query->where('is_published', true),
                'exercises as published_exercises_count' => fn ($query) => $query->where('is_published', true),
            ])
            ->where('is_published', true)
            ->whereIn('subject', $this->enrollmentSubjects($enrollment))
            ->when($enrollment->level, fn ($query, string $level) => $query->where('level', $level))
            ->orderBy('sort_order')
            ->get();

        $quizzes = SummerClubQuiz::query()
            ->withCount('questions')
            ->with('resource')
            ->where('is_published', true)
            ->where(function ($query) use ($enrollment) {
                $subjects = $this->enrollmentSubjects($enrollment);
                $query->where(function ($query) use ($subjects) {
                    $query->whereIn('subject', $subjects)
                        ->orWhereHas('resource', fn ($query) => $query->whereIn('subject', $subjects));
                });
            })
            ->when($enrollment->level, function ($query, string $level) {
                $query->where(function ($query) use ($level) {
                    $query->where('level', $level)
                        ->orWhereHas('resource', fn ($query) => $query->where('level', $level));
                });
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $exercises = SummerClubExercise::query()
            ->withCount('items')
            ->with('resource')
            ->where('is_published', true)
            ->where(function ($query) use ($enrollment) {
                $subjects = $this->enrollmentSubjects($enrollment);
                $query->where(function ($query) use ($subjects) {
                    $query->whereIn('subject', $subjects)
                        ->orWhereHas('resource', fn ($query) => $query->whereIn('subject', $subjects));
                });
            })
            ->when($enrollment->level, function ($query, string $level) {
                $query->where(function ($query) use ($level) {
                    $query->where('level', $level)
                        ->orWhereHas('resource', fn ($query) => $query->where('level', $level));
                });
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('student.summer-club.catalogue', [
            'resources' => $resources,
            'quizzes' => $quizzes,
            'exercises' => $exercises,
            'quizAttempts' => $this->bestQuizAttempts($request, $quizzes->pluck('id')->all(), $enrollment),
            'exerciseAttempts' => $this->bestExerciseAttempts($request, $exercises->pluck('id')->all(), $enrollment),
        ]);
    }

    public function formations(Request $request)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        if ($this->enrollmentSubjects($enrollment) === []) {
            return view('student.summer-club.locked', [
                'message' => 'Votre accès Club d’été est actif, mais aucune matière n’est encore associée. Veuillez contacter l’administration.',
            ]);
        }

        $resources = SummerClubResource::query()
            ->withCount([
                'quizzes as published_quizzes_count' => fn ($query) => $query->where('is_published', true),
                'exercises as published_exercises_count' => fn ($query) => $query->where('is_published', true),
            ])
            ->where('is_published', true)
            ->whereIn('subject', $this->enrollmentSubjects($enrollment))
            ->when($enrollment->level, fn ($query, string $level) => $query->where('level', $level))
            ->orderBy('sort_order')
            ->get();

        return view('student.summer-club.formations.index', compact('resources'));
    }

    public function showFormation(Request $request, SummerClubResource $resource)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        abort_unless($resource->is_published, 404);
        abort_unless($this->canAccessResource($enrollment, $resource), 404);

        $resource->load([
            'quizzes' => fn ($query) => $query
                ->withCount('questions')
                ->where('is_published', true)
                ->where(function ($query) use ($enrollment) {
                    $query->whereIn('subject', $this->enrollmentSubjects($enrollment))
                        ->orWhereNull('subject');
                })
                ->when($enrollment->level, fn ($query, string $level) => $query->where(function ($query) use ($level) {
                    $query->where('level', $level)->orWhereNull('level');
                }))
                ->orderBy('sort_order'),
            'exercises' => fn ($query) => $query
                ->withCount('items')
                ->where('is_published', true)
                ->where(function ($query) use ($enrollment) {
                    $query->whereIn('subject', $this->enrollmentSubjects($enrollment))
                        ->orWhereNull('subject');
                })
                ->when($enrollment->level, fn ($query, string $level) => $query->where(function ($query) use ($level) {
                    $query->where('level', $level)->orWhereNull('level');
                }))
                ->orderBy('sort_order'),
        ]);

        return view('student.summer-club.formations.show', [
            'resource' => $resource,
            'quizAttempts' => $this->bestQuizAttempts($request, $resource->quizzes->pluck('id')->all(), $enrollment),
            'exerciseAttempts' => $this->bestExerciseAttempts($request, $resource->exercises->pluck('id')->all(), $enrollment),
        ]);
    }

    public function quiz(Request $request, SummerClubQuiz $quiz)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        abort_unless($quiz->is_published, 404);

        $quiz->load([
            'resource',
            'questions' => fn ($query) => $query->orderBy('sort_order'),
        ]);
        abort_unless($this->canAccessItem($enrollment, $quiz->subject ?: $quiz->resource?->subject, $quiz->level ?: $quiz->resource?->level), 404);

        return view('student.summer-club.quiz', [
            'quiz' => $quiz,
            'questions' => $quiz->questions->map(fn ($question) => [
                'id' => $question->id,
                'question' => $question->question,
                'media_type' => $question->media_type,
                'media_url' => $question->media_url,
                'media_path_url' => $question->media_path ? Storage::disk('public')->url($question->media_path) : null,
                'options' => [
                    'a' => $question->option_a,
                    'b' => $question->option_b,
                    'c' => $question->option_c,
                    'd' => $question->option_d,
                ],
                'points' => $question->points,
            ])->values(),
            'submitUrl' => route('student.club-ete.quiz.submit', $quiz),
            'lastAttempt' => $this->bestQuizAttempts($request, [$quiz->id], $enrollment)->get($quiz->id),
        ]);
    }

    public function submitQuiz(Request $request, SummerClubQuiz $quiz)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        abort_unless($quiz->is_published, 404);

        $quiz->load(['resource', 'questions' => fn ($query) => $query->orderBy('sort_order')]);
        abort_unless($this->canAccessItem($enrollment, $quiz->subject ?: $quiz->resource?->subject, $quiz->level ?: $quiz->resource?->level), 404);

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'started_at' => ['nullable', 'date'],
        ]);

        $answers = $validated['answers'];
        $total = (int) $quiz->questions->sum(fn ($question) => max(1, (int) $question->points));
        $score = $quiz->questions->sum(function ($question) use ($answers) {
            $answer = $answers[$question->id] ?? null;

            return $this->hasAnswerValue($question->correct_option)
                && $this->hasAnswerValue($answer)
                && $this->sameAnswer($answer, $question->correct_option)
                ? max(1, (int) $question->points)
                : 0;
        });
        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;

        $attempt = SummerClubQuizAttempt::create([
            'user_id' => $request->user()->id,
            'summer_club_quiz_id' => $quiz->id,
            'summer_club_enrollment_id' => $enrollment->id,
            'answers' => $answers,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $percentage >= 50,
            'started_at' => $validated['started_at'] ?? now(),
            'completed_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tentative enregistrée.',
                'score' => $attempt->score,
                'total' => $attempt->total,
                'percentage' => (float) $attempt->percentage,
                'passed' => $attempt->passed,
                'completed_at' => $attempt->completed_at?->format('Y-m-d H:i'),
            ]);
        }

        return back()->with('summer_club_result', [
            'score' => $attempt->score,
            'total' => $attempt->total,
            'percentage' => (float) $attempt->percentage,
            'passed' => $attempt->passed,
        ]);
    }

    public function showExercise(Request $request, SummerClubExercise $exercise)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        abort_unless($exercise->is_published, 404);

        $exercise->load([
            'resource',
            'items' => fn ($query) => $query->orderBy('sort_order'),
        ]);
        abort_unless($this->canAccessItem($enrollment, $exercise->subject ?: $exercise->resource?->subject, $exercise->level ?: $exercise->resource?->level), 404);

        return view('student.summer-club.exercise', [
            'exercise' => $exercise,
            'items' => $exercise->items->map(fn ($item) => [
                'id' => $item->id,
                'type' => $item->type,
                'instruction' => $item->instruction,
                'question' => $item->question,
                'media_type' => $item->media_type,
                'media_url' => $item->media_url,
                'media_path_url' => $item->media_path ? Storage::disk('public')->url($item->media_path) : null,
                'options' => $item->options,
                'points' => $item->points,
            ])->values(),
            'submitUrl' => route('student.club-ete.exercise.submit', $exercise),
            'lastAttempt' => $this->bestExerciseAttempts($request, [$exercise->id], $enrollment)->get($exercise->id),
        ]);
    }

    public function submitExercise(Request $request, SummerClubExercise $exercise)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        abort_unless($exercise->is_published, 404);

        $exercise->load(['resource', 'items' => fn ($query) => $query->orderBy('sort_order')]);
        abort_unless($this->canAccessItem($enrollment, $exercise->subject ?: $exercise->resource?->subject, $exercise->level ?: $exercise->resource?->level), 404);

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'started_at' => ['nullable', 'date'],
        ]);

        $answers = $validated['answers'];
        $total = (int) $exercise->items->sum(fn ($item) => max(1, (int) $item->points));
        $score = $exercise->items->sum(function ($item) use ($answers) {
            return $this->isExerciseItemCorrect($item, $answers[$item->id] ?? null)
                ? max(1, (int) $item->points)
                : 0;
        });
        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;

        $attempt = SummerClubExerciseAttempt::create([
            'user_id' => $request->user()->id,
            'summer_club_exercise_id' => $exercise->id,
            'summer_club_enrollment_id' => $enrollment->id,
            'answers' => $answers,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $percentage >= 50,
            'started_at' => $validated['started_at'] ?? now(),
            'completed_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tentative enregistrée.',
                'score' => $attempt->score,
                'total' => $attempt->total,
                'percentage' => (float) $attempt->percentage,
                'passed' => $attempt->passed,
                'completed_at' => $attempt->completed_at?->format('Y-m-d H:i'),
            ]);
        }

        return back()->with('summer_club_result', [
            'score' => $attempt->score,
            'total' => $attempt->total,
            'percentage' => (float) $attempt->percentage,
            'passed' => $attempt->passed,
        ]);
    }

    private function activeSummerClubEnrollment(Request $request): ?SummerClubEnrollment
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        /*
         * Le Club d'ete utilise une logique d'abonnement separee des courses classiques.
         * L'acces est accorde uniquement via summer_club_enrollments, sans utiliser courses/enrollments.
         */
        return SummerClubEnrollment::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest('confirmed_at')
            ->first();
    }

    private function enrollmentSubjects(SummerClubEnrollment $enrollment): array
    {
        return array_values(array_filter($enrollment->selected_subjects ?? []));
    }

    private function canAccessResource(SummerClubEnrollment $enrollment, SummerClubResource $resource): bool
    {
        return $this->canAccessItem($enrollment, $resource->subject, $resource->level);
    }

    private function canAccessSubject(SummerClubEnrollment $enrollment, ?string $subject): bool
    {
        $subjects = $this->enrollmentSubjects($enrollment);

        if ($subjects === []) {
            return false;
        }

        return $subject !== null && in_array($subject, $subjects, true);
    }

    private function canAccessItem(SummerClubEnrollment $enrollment, ?string $subject, ?string $level): bool
    {
        if (! $this->canAccessSubject($enrollment, $subject)) {
            return false;
        }

        if (! $enrollment->level) {
            return true;
        }

        return $level === $enrollment->level;
    }

    private function bestQuizAttempts(Request $request, array $quizIds, SummerClubEnrollment $enrollment)
    {
        if ($quizIds === []) {
            return collect();
        }

        return SummerClubQuizAttempt::query()
            ->where('user_id', $request->user()->id)
            ->where('summer_club_enrollment_id', $enrollment->id)
            ->whereIn('summer_club_quiz_id', $quizIds)
            ->orderByDesc('percentage')
            ->orderByDesc('completed_at')
            ->get()
            ->unique('summer_club_quiz_id')
            ->keyBy('summer_club_quiz_id');
    }

    private function bestExerciseAttempts(Request $request, array $exerciseIds, SummerClubEnrollment $enrollment)
    {
        if ($exerciseIds === []) {
            return collect();
        }

        return SummerClubExerciseAttempt::query()
            ->where('user_id', $request->user()->id)
            ->where('summer_club_enrollment_id', $enrollment->id)
            ->whereIn('summer_club_exercise_id', $exerciseIds)
            ->orderByDesc('percentage')
            ->orderByDesc('completed_at')
            ->get()
            ->unique('summer_club_exercise_id')
            ->keyBy('summer_club_exercise_id');
    }

    private function isExerciseItemCorrect($item, mixed $answer): bool
    {
        $correct = $item->correct_answer ?? [];

        if (! is_array($correct) || ! $this->hasAnswerValue($answer)) {
            return false;
        }

        return match ($item->type) {
            'multiple_choice' => $this->expectedList($correct['answers'] ?? []) !== []
                && $this->sameStringSet($answer, $this->expectedList($correct['answers'] ?? [])),
            'true_false' => $this->hasAnswerValue($correct['answer'] ?? null)
                && $this->sameAnswer($answer, $correct['answer']),
            'fill_blank', 'short_answer' => $this->expectedList($correct['answers'] ?? []) !== []
                && $this->answerInList($answer, $this->expectedList($correct['answers'] ?? [])),
            'matching' => $this->expectedList($correct['pairs'] ?? []) !== []
                && $this->sameAssociativePairs($answer, $this->expectedList($correct['pairs'] ?? []), 'left', 'right'),
            'ordering' => $this->expectedList($correct['order'] ?? []) !== []
                && $this->sameStringList($answer, $this->expectedList($correct['order'] ?? [])),
            'drag_drop' => $this->expectedList($correct['matches'] ?? []) !== []
                && $this->sameAssociativePairs($answer, $this->expectedList($correct['matches'] ?? []), 'item', 'zone'),
            'image_labeling' => $this->expectedList($correct['answers'] ?? []) !== []
                && $this->sameAssociativePairs($answer, $this->expectedList($correct['answers'] ?? []), 'label', 'answer', normalizeValues: true),
            default => false,
        };
    }

    private function sameAnswer(mixed $given, mixed $expected): bool
    {
        return $this->normalizeAnswer($given) === $this->normalizeAnswer($expected);
    }

    private function answerInList(mixed $given, array $expectedAnswers): bool
    {
        $given = $this->normalizeAnswer($given);

        return collect($expectedAnswers)
            ->map(fn ($answer) => $this->normalizeAnswer($answer))
            ->contains($given);
    }

    private function sameStringSet(mixed $given, array $expected): bool
    {
        $given = is_array($given) ? $given : [$given];

        $given = collect($given)
            ->filter(fn ($value) => $this->hasAnswerValue($value))
            ->map(fn ($value) => $this->normalizeAnswer($value))
            ->sort()
            ->values()
            ->all();
        $expected = collect($expected)
            ->filter(fn ($value) => $this->hasAnswerValue($value))
            ->map(fn ($value) => $this->normalizeAnswer($value))
            ->sort()
            ->values()
            ->all();

        if ($given === [] || $expected === []) {
            return false;
        }

        return $given === $expected;
    }

    private function sameStringList(mixed $given, array $expected): bool
    {
        if (! is_array($given) || $given === [] || $expected === []) {
            return false;
        }

        $given = array_values(array_map(fn ($value) => $this->normalizeAnswer($value), $given));
        $expected = array_values(array_map(fn ($value) => $this->normalizeAnswer($value), $expected));

        return $given === $expected;
    }

    private function sameAssociativePairs(mixed $given, array $expected, string $leftKey, string $rightKey, bool $normalizeValues = false): bool
    {
        if (! is_array($given) || $given === [] || $expected === []) {
            return false;
        }

        foreach ($expected as $pair) {
            $left = $pair[$leftKey] ?? null;
            $right = $pair[$rightKey] ?? null;

            if ($left === null || $right === null || ! array_key_exists($left, $given)) {
                return false;
            }

            $givenValue = $given[$left];

            if ($normalizeValues) {
                if (! $this->sameAnswer($givenValue, $right)) {
                    return false;
                }
            } elseif ((string) $givenValue !== (string) $right) {
                return false;
            }
        }

        return true;
    }

    private function normalizeAnswer(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $value)) ?? '');
    }

    private function hasAnswerValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return true;
        }

        if (is_array($value)) {
            return collect($value)->contains(fn ($item) => $this->hasAnswerValue($item));
        }

        return trim((string) $value) !== '';
    }

    private function expectedList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, fn ($item) => $this->hasAnswerValue($item)));
    }
}
