<?php

namespace App\Http\Controllers;

use App\Models\CoursePackItem;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizScoringService;
use Illuminate\Http\Request;

class QuizPlayerController extends Controller
{
    /**
     * Verify the authenticated user may access this quiz.
     * A paid course requires an active enrollment.
     */
    private function authorizeQuizAccess(Quiz $quiz): void
    {
        abort_unless($quiz->is_published, 404);

        $user = auth()->user();

        if ($quiz->course_id) {
            // Quiz rattaché directement à un cours simple
            $course = $quiz->relationLoaded('course')
                ? $quiz->course
                : $quiz->course()->first();

            if ($course && $course->is_paid) {
                $hasAccess = $user->hasActiveEnrollmentForCourse($course->id);

                // Vérifier l'accès via un pack qui contient ce cours
                if (! $hasAccess) {
                    $packIds = CoursePackItem::where('linked_course_id', $course->id)->pluck('pack_id');
                    foreach ($packIds as $packId) {
                        if ($user->hasActiveEnrollmentForCourse($packId)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                abort_unless($hasAccess, 403, 'Accès refusé : vous devez être inscrit à ce cours.');
            }
        } elseif ($quiz->course_pack_item_id) {
            // Quiz rattaché à un item de pack — vérifier l'inscription au pack
            $packItem = $quiz->relationLoaded('packItem')
                ? $quiz->packItem
                : $quiz->packItem()->first();

            if ($packItem) {
                $pack = $packItem->relationLoaded('pack')
                    ? $packItem->pack
                    : $packItem->pack()->first();

                if ($pack && $pack->is_paid) {
                    abort_unless(
                        $user->hasActiveEnrollmentForCourse($pack->id),
                        403,
                        'Accès refusé : vous devez être inscrit à ce cours.'
                    );
                }
            }
        }
    }

    public function show(Quiz $quiz)
    {
        $quiz->load([
            'questions.options',
            'course',
            'packItem.pack',
        ]);

        $this->authorizeQuizAccess($quiz);

        if ($quiz->max_attempts) {
            $attemptsCount = $quiz->attempts()
                ->where('user_id', auth()->id())
                ->count();

            if ($attemptsCount >= $quiz->max_attempts) {
                return redirect()
                    ->route('quiz.result', $quiz->attempts()
                        ->where('user_id', auth()->id())
                        ->latest()
                        ->firstOrFail())
                    ->with('warning', 'Nombre maximum de tentatives atteint.');
            }
        }

        return view('quiz.show', [
            'quiz' => $quiz,
        ]);
    }

    public function submit(Request $request, Quiz $quiz, QuizScoringService $scoringService)
    {
        $quiz->load(['questions.options', 'course']);

        $this->authorizeQuizAccess($quiz);

        if ($quiz->max_attempts) {
            $attemptsCount = $quiz->attempts()
                ->where('user_id', auth()->id())
                ->count();

            abort_if($attemptsCount >= $quiz->max_attempts, 403, 'Nombre maximum de tentatives atteint.');
        }

        $validated = $request->validate([
            'answers' => ['array'],
        ]);

        $attempt = $scoringService->submit(
            $quiz,
            $validated['answers'] ?? [],
            auth()->id()
        );

        return redirect()->route('quiz.result', $attempt);
    }

    public function result(QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $attempt->load([
            'quiz.course',
            'quiz.packItem.pack',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        return view('quiz.result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
        ]);
    }
}