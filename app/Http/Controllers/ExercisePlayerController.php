<?php

namespace App\Http\Controllers;

use App\Models\CoursePackItem;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\ExerciseAttemptAnswer;
use App\Models\ExerciseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExercisePlayerController extends Controller
{
    public function show(Exercise $exercise)
    {
        $exercise->load(['items.answers', 'course', 'packItem.pack']);

        abort_unless($exercise->is_published, 404);

        $this->checkAccess($exercise);

        if ($exercise->exercise_type === 'pdf') {
            abort_unless(filled($exercise->pdf_path), 404);

            return view('exercises.pdf', compact('exercise'));
        }

        $lastAttempt = ExerciseAttempt::where('user_id', auth()->id())
            ->where('exercise_id', $exercise->id)
            ->where('is_completed', true)
            ->latest()
            ->first();

        return view('exercises.show', compact('exercise', 'lastAttempt'));
    }

    public function submit(Request $request, Exercise $exercise)
    {
        $exercise->load(['items.answers', 'course', 'packItem.pack']);

        abort_unless($exercise->is_published, 404);
        abort_if($exercise->exercise_type === 'pdf', 403);

        $this->checkAccess($exercise);

        $maxScore = $exercise->items->sum('points');
        $answers = $request->input('answers', []);

        $attempt = DB::transaction(function () use ($exercise, $maxScore, $answers) {
            $attempt = ExerciseAttempt::create([
                'user_id' => auth()->id(),
                'exercise_id' => $exercise->id,
                'score' => 0,
                'max_score' => $maxScore,
                'is_completed' => false,
                'started_at' => now(),
            ]);

            $totalScore = 0;

            foreach ($exercise->items as $item) {
                $userAnswer = $answers[$item->id] ?? null;

                [$isCorrect, $pointsEarned, $answerData] = $this->evaluate($item, $userAnswer);

                ExerciseAttemptAnswer::create([
                    'exercise_attempt_id' => $attempt->id,
                    'exercise_item_id' => $item->id,
                    'answer_data' => $answerData,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);

                $totalScore += $pointsEarned;
            }

            $attempt->update([
                'score' => $totalScore,
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            return $attempt;
        });

        return redirect()->route('exercise.result', $attempt);
    }

    public function result(ExerciseAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $attempt->load([
            'exercise',
            'answers.item.answers',
        ]);

        $exercise = $attempt->exercise;

        return view('exercises.result', compact('attempt', 'exercise'));
    }

    private function evaluate(ExerciseItem $item, mixed $userAnswer): array
    {
        $points = $item->points;

        switch ($item->item_type) {
            case 'single_choice':
            case 'true_false':
                $answerId = (int) $userAnswer;
                $answer = $item->answers->firstWhere('id', $answerId);
                $ok = $answer?->is_correct ?? false;

                return [$ok, $ok ? $points : 0, ['answer_id' => $answerId]];

            case 'multiple_choice':
                $selected = array_map('intval', (array) ($userAnswer ?? []));
                $correctIds = $item->answers
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();

                sort($selected);

                $ok = $selected === $correctIds;

                return [$ok, $ok ? $points : 0, ['answer_ids' => $selected]];

            case 'matching':
                $pairs = (array) ($userAnswer ?? []);
                $allOk = true;

                foreach ($item->answers as $answer) {
                    $chosen = trim(strtolower($pairs[$answer->id] ?? ''));
                    $expect = trim(strtolower($answer->match_text ?? ''));

                    if ($chosen !== $expect) {
                        $allOk = false;
                        break;
                    }
                }

                return [$allOk, $allOk ? $points : 0, ['pairs' => $pairs]];

            case 'ordering':
                $positions = (array) ($userAnswer ?? []);
                $allOk = true;

                foreach ($item->answers as $answer) {
                    $chosen = (int) ($positions[$answer->id] ?? 0);

                    if ($chosen !== (int) $answer->sort_order) {
                        $allOk = false;
                        break;
                    }
                }

                return [$allOk, $allOk ? $points : 0, ['positions' => $positions]];

            case 'fill_blank':
                $blanks = (array) ($userAnswer ?? []);
                $correct = $item->answers
                    ->where('is_correct', true)
                    ->sortBy('blank_index');

                $allOk = $correct->isNotEmpty();

                foreach ($correct as $ans) {
                    $idx = $ans->blank_index ?? 0;
                    $given = trim(strtolower($blanks[$idx] ?? ''));
                    $expect = trim(strtolower($ans->answer_text ?? ''));

                    if ($given !== $expect) {
                        $allOk = false;
                        break;
                    }
                }

                return [$allOk, $allOk ? $points : 0, ['blanks' => $blanks]];

            case 'word_bank':
                $selected = array_map('intval', (array) ($userAnswer ?? []));
                $correctIds = $item->answers
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();

                sort($selected);

                $ok = $selected === $correctIds;

                return [$ok, $ok ? $points : 0, ['answer_ids' => $selected]];

            case 'categorization':
                $cats = (array) ($userAnswer ?? []);
                $allOk = true;

                foreach ($item->answers as $answer) {
                    $chosen = trim(strtolower($cats[$answer->id] ?? ''));
                    $expect = trim(strtolower($answer->category ?? ''));

                    if ($chosen !== $expect) {
                        $allOk = false;
                        break;
                    }
                }

                return [$allOk, $allOk ? $points : 0, ['categories' => $cats]];

            case 'short_answer':
                $given = trim(strtolower((string) ($userAnswer ?? '')));
                $accepted = $item->answers->where('is_correct', true);
                $ok = false;

                if (! empty($given)) {
                    foreach ($accepted as $ans) {
                        $expect = trim(strtolower($ans->answer_text ?? ''));
                        $mode = $ans->match_mode ?? 'exact';

                        $match = match ($mode) {
                            'contains' => str_contains($given, $expect),
                            'starts_with' => str_starts_with($given, $expect),
                            default => $given === $expect,
                        };

                        if ($match) {
                            $ok = true;
                            break;
                        }
                    }
                }

                return [$ok, $ok ? $points : 0, ['text' => $userAnswer]];

            case 'calculation':
                $given = (float) ($userAnswer ?? 0);
                $ans = $item->answers->first();

                if (! $ans) {
                    return [false, 0, ['value' => $userAnswer]];
                }

                $ok = abs($given - (float) $ans->correct_value) <= (float) ($ans->tolerance ?? 0);

                return [$ok, $ok ? $points : 0, ['value' => $given]];

            default:
                return [false, 0, ['raw' => $userAnswer]];
        }
    }

    private function checkAccess(Exercise $exercise): void
    {
        $user = auth()->user();

        abort_unless($user, 403, 'Connexion requise.');

        /*
        |--------------------------------------------------------------------------
        | Cas 1 : exercice lié à un cours simple
        |--------------------------------------------------------------------------
        */
        if ($exercise->course_id) {
            $course = $exercise->relationLoaded('course')
                ? $exercise->course
                : $exercise->course()->first();

            if ($course) {
                abort_unless($course->is_published, 404);

                if ($course->is_paid) {
                    $hasAccess = $user->hasActiveEnrollmentForCourse($course->id);

                    if (! $hasAccess) {
                        $packIds = CoursePackItem::where('linked_course_id', $course->id)->pluck('pack_id');

                        foreach ($packIds as $packId) {
                            if ($user->hasActiveEnrollmentForCourse($packId)) {
                                $hasAccess = true;
                                break;
                            }
                        }
                    }

                    abort_unless($hasAccess, 403, 'Accès refusé : inscription requise.');
                }
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Cas 2 : exercice lié à un item de pack
        |--------------------------------------------------------------------------
        */
        if ($exercise->course_pack_item_id) {
            $packCourse = $exercise->relationLoaded('packItem')
                ? $exercise->packItem?->pack
                : $exercise->packItem()->first()?->pack;

            if ($packCourse) {
                abort_unless($packCourse->is_published, 404);

                if ($packCourse->is_paid) {
                    abort_unless(
                        $user->hasActiveEnrollmentForCourse($packCourse->id),
                        403,
                        'Accès refusé : inscription au pack requise.'
                    );
                }
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Cas 3 : exercice seul
        |--------------------------------------------------------------------------
        */
        if ((bool) ($exercise->is_paid ?? true)) {
            $hasAccess = method_exists($user, 'hasActiveEnrollmentForExercise')
                && $user->hasActiveEnrollmentForExercise($exercise->id);

            abort_unless($hasAccess, 403, 'Accès refusé : achat de l’exercice requis.');
        }
    }
}