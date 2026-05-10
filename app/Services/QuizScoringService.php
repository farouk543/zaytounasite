<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Support\Facades\DB;

class QuizScoringService
{
    public function submit(Quiz $quiz, array $answers, int $userId): QuizAttempt
    {
        return DB::transaction(function () use ($quiz, $answers, $userId) {
            $attemptNumber = (int) $quiz->attempts()
                ->where('user_id', $userId)
                ->max('attempt_number') + 1;

            $maxScore = (int) $quiz->questions()->sum('points');

            $attempt = QuizAttempt::create([
                'quiz_id'        => $quiz->id,
                'user_id'        => $userId,
                'attempt_number' => $attemptNumber,
                'score'          => 0,
                'max_score'      => $maxScore,
                'percentage'     => 0,
                'is_passed'      => false,
                'started_at'     => now(),
                'submitted_at'   => now(),
            ]);

            $score = 0;

            $questions = $quiz->questions()->with('options')->get();

            foreach ($questions as $question) {
                $given = $answers[$question->id] ?? null;

                [$isCorrect, $pointsEarned, $selectedOptionId, $answerText] =
                    $this->scoreQuestion($question, $given);

                QuizAttemptAnswer::create([
                    'quiz_attempt_id'  => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'selected_option_id' => $selectedOptionId,
                    'answer_text'      => $answerText,
                    'is_correct'       => $isCorrect,
                    'points_earned'    => $pointsEarned,
                ]);

                $score += $pointsEarned;
            }

            $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;
            $isPassed   = $percentage >= $quiz->passing_score;

            $attempt->update([
                'score'      => $score,
                'percentage' => $percentage,
                'is_passed'  => $isPassed,
            ]);

            return $attempt->fresh(['answers.question', 'answers.selectedOption']);
        });
    }

    private function scoreQuestion($question, mixed $given): array
    {
        $isCorrect       = false;
        $pointsEarned    = 0;
        $selectedOptionId = null;
        $answerText      = null;

        switch ($question->question_type) {

            case 'single_choice':
            case 'true_false':
                $selectedOptionId = is_numeric($given) ? (int) $given : null;
                $correctOption    = $question->options->firstWhere('is_correct', true);
                $isCorrect        = $correctOption && $correctOption->id === $selectedOptionId;
                $pointsEarned     = $isCorrect ? (int) $question->points : 0;
                break;

            case 'multiple_choice':
                // $given is an array of selected option IDs
                $selectedIds = is_array($given) ? array_map('intval', $given) : [];
                $correctIds  = $question->options->where('is_correct', true)->pluck('id')->toArray();
                $wrongIds    = $question->options->where('is_correct', false)->pluck('id')->toArray();

                // All-or-nothing: must pick exactly the right options
                $isCorrect    = empty(array_diff($correctIds, $selectedIds))
                             && empty(array_intersect($wrongIds, $selectedIds));
                $pointsEarned = $isCorrect ? (int) $question->points : 0;
                $answerText   = json_encode($selectedIds);
                break;

            case 'short_answer':
                $answerText   = is_string($given) ? trim($given) : null;
                // Free text — not auto-graded
                $isCorrect    = false;
                $pointsEarned = 0;
                break;

            case 'fill_blank':
                // Compare typed answer (case-insensitive) to any correct option text
                $typed = is_string($given) ? mb_strtolower(trim($given)) : null;
                $correctTexts = $question->options
                    ->where('is_correct', true)
                    ->map(fn ($o) => mb_strtolower(trim($o->option_text)))
                    ->toArray();

                $isCorrect    = $typed && in_array($typed, $correctTexts, true);
                $pointsEarned = $isCorrect ? (int) $question->points : 0;
                $answerText   = $typed;
                break;

            case 'matching':
                // $given = ['option_id' => 'matched_match_text', ...]
                $pairs = is_array($given) ? $given : [];
                $totalPairs  = $question->options->count();
                $correctCount = 0;

                foreach ($question->options as $option) {
                    $studentAnswer = $pairs[$option->id] ?? null;
                    if ($studentAnswer !== null && (string) $studentAnswer === (string) $option->match_text) {
                        $correctCount++;
                    }
                }

                $isCorrect    = $totalPairs > 0 && $correctCount === $totalPairs;
                // Partial credit: proportional
                $pointsEarned = $totalPairs > 0
                    ? (int) round(($correctCount / $totalPairs) * $question->points)
                    : 0;
                $answerText   = json_encode($pairs);
                break;

            case 'ordering':
                // $given = JSON string of option IDs in student's order
                $studentOrder = is_string($given) ? json_decode($given, true) : (is_array($given) ? $given : []);
                $studentOrder = array_map('intval', (array) $studentOrder);

                // Correct order = options sorted by sort_order
                $correctOrder = $question->options
                    ->sortBy('sort_order')
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                $isCorrect    = $studentOrder === $correctOrder;
                $pointsEarned = $isCorrect ? (int) $question->points : 0;
                $answerText   = json_encode($studentOrder);
                break;
        }

        return [$isCorrect, $pointsEarned, $selectedOptionId, $answerText];
    }
}
