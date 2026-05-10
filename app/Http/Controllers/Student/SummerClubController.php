<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SummerClubEnrollment;
use App\Models\SummerClubExercise;
use App\Models\SummerClubQuiz;
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

        $resources = SummerClubResource::query()
            ->withCount([
                'quizzes as published_quizzes_count' => fn ($query) => $query->where('is_published', true),
                'exercises as published_exercises_count' => fn ($query) => $query->where('is_published', true),
            ])
            ->where('is_published', true)
            ->when($this->enrollmentSubjects($enrollment), fn ($query, array $subjects) => $query->whereIn('subject', $subjects))
            ->orderBy('sort_order')
            ->get();

        $quizzes = SummerClubQuiz::query()
            ->withCount('questions')
            ->with('resource')
            ->where('is_published', true)
            ->when($this->enrollmentSubjects($enrollment), function ($query, array $subjects) {
                $query->where(function ($query) use ($subjects) {
                    $query->whereIn('subject', $subjects)
                        ->orWhereHas('resource', fn ($query) => $query->whereIn('subject', $subjects));
                });
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $exercises = SummerClubExercise::query()
            ->withCount('items')
            ->with('resource')
            ->where('is_published', true)
            ->when($this->enrollmentSubjects($enrollment), function ($query, array $subjects) {
                $query->where(function ($query) use ($subjects) {
                    $query->whereIn('subject', $subjects)
                        ->orWhereHas('resource', fn ($query) => $query->whereIn('subject', $subjects));
                });
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('student.summer-club.catalogue', compact('resources', 'quizzes', 'exercises'));
    }

    public function formations(Request $request)
    {
        $enrollment = $this->activeSummerClubEnrollment($request);

        if (! $enrollment) {
            return view('student.summer-club.locked');
        }

        $resources = SummerClubResource::query()
            ->withCount([
                'quizzes as published_quizzes_count' => fn ($query) => $query->where('is_published', true),
                'exercises as published_exercises_count' => fn ($query) => $query->where('is_published', true),
            ])
            ->where('is_published', true)
            ->when($this->enrollmentSubjects($enrollment), fn ($query, array $subjects) => $query->whereIn('subject', $subjects))
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
                ->orderBy('sort_order'),
            'exercises' => fn ($query) => $query
                ->withCount('items')
                ->where('is_published', true)
                ->orderBy('sort_order'),
        ]);

        return view('student.summer-club.formations.show', compact('resource'));
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
        abort_unless($this->canAccessSubject($enrollment, $quiz->subject ?: $quiz->resource?->subject), 404);

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
                'correct' => $question->correct_option,
                'explanation' => $question->explanation,
                'points' => $question->points,
            ])->values(),
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
        abort_unless($this->canAccessSubject($enrollment, $exercise->subject ?: $exercise->resource?->subject), 404);

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
                'correct_answer' => $item->correct_answer,
                'explanation' => $item->explanation,
                'points' => $item->points,
            ])->values(),
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
        return $this->canAccessSubject($enrollment, $resource->subject);
    }

    private function canAccessSubject(SummerClubEnrollment $enrollment, ?string $subject): bool
    {
        $subjects = $this->enrollmentSubjects($enrollment);

        if ($subjects === []) {
            return true;
        }

        return $subject !== null && in_array($subject, $subjects, true);
    }
}
