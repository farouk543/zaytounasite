<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CoursePackItemProgress;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Pour admin / manager / teacher, la vue peut rester simple
        if (
            $user->hasRole('admin') ||
            $user->hasRole('manager') ||
            $user->hasRole('teacher')
        ) {
            return view('dashboard', [
                'dashboardStats' => [
                    'active_products' => 0,
                    'completed_quizzes' => 0,
                    'average_score' => 0,
                    'average_progress' => 0,
                ],
                'learningProducts' => collect(),
                'recentQuizAttempts' => collect(),
                'continueUrl' => route('my.courses'),
                'continueLabel' => __('ui.nav.my_courses'),
            ]);
        }

        // Enrollments actifs
        $enrollments = Enrollment::query()
            ->with([
                'course.subject.level.track',
                'course.packItems',
                'course.quizzes',
                'course.exercises',
            ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $courses = $enrollments
            ->pluck('course')
            ->filter()
            ->values();

        $activeProducts = $courses->count();

        // Progression produits
        $learningProducts = $courses->map(function ($course) use ($user) {
            $isPack = $course->isPack();

            $progress = 0;
            $typeLabel = $isPack ? __('ui.type_course_pack') : __('ui.type_course');

            if ($isPack) {
                $requiredItems = $course->packItems->where('is_required', true);
                $totalRequired = $requiredItems->count();

                if ($totalRequired > 0) {
                    $completed = CoursePackItemProgress::query()
                        ->where('user_id', $user->id)
                        ->where('pack_id', $course->id)
                        ->where('is_completed', true)
                        ->count();

                    $progress = (int) round(($completed / $totalRequired) * 100);
                }
            } else {
                // Course simple : on considère 0 tant qu'on n'a pas de tracking détaillé
                $progress = 0;
            }

            // Quiz / exercices directs (cours simples uniquement)
            $quizzesCount   = $course->isSimpleCourse() ? $course->quizzes->where('is_published', true)->count() : 0;
            $exercisesCount = $course->isSimpleCourse() ? $course->exercises->where('is_published', true)->count() : 0;

            return [
                'title'           => $course->title_i18n ?? $course->title_display ?? $course->title,
                'type_label'      => $typeLabel,
                'progress'        => $progress,
                'url'             => route('courses.access', $course),
                'meta'            => $course->subject?->name_i18n ?? $course->subject?->name,
                'quizzes_count'   => $quizzesCount,
                'exercises_count' => $exercisesCount,
            ];
        })->sortByDesc('progress')->values();

        // Tentatives quiz récentes
        $attempts = QuizAttempt::query()
            ->with(['quiz.packItem.pack', 'quiz.course'])
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $recentQuizAttempts = $attempts->map(function ($attempt) {
            return [
                'title' => $attempt->quiz?->title_display ?? $attempt->quiz?->title ?? 'Quiz',
                'percentage' => (int) round((float) $attempt->percentage),
                'status' => $attempt->is_passed ? 'Validé' : 'À renforcer',
                'url' => route('quiz.result', $attempt),
            ];
        });

        $completedQuizzes = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('is_passed', true)
            ->count();

        $averageScore = (int) round(
            (float) (QuizAttempt::query()
                ->where('user_id', $user->id)
                ->avg('percentage') ?? 0)
        );

        $averageProgress = $learningProducts->count() > 0
            ? (int) round($learningProducts->avg('progress'))
            : 0;

        $dashboardStats = [
            'active_products' => $activeProducts,
            'completed_quizzes' => $completedQuizzes,
            'average_score' => $averageScore,
            'average_progress' => $averageProgress,
        ];

        // Bouton Continuer : produit avec la meilleure progression incomplète
        $continueProduct = $learningProducts
            ->sortByDesc('progress')
            ->first(fn ($product) => ($product['progress'] ?? 0) < 100);

        $continueUrl = $continueProduct['url'] ?? route('my.courses');
        $continueLabel = $continueProduct['title'] ?? __('ui.nav.my_courses');
        $continueMeta = $continueProduct['meta'] ?? null;
        $continueType = $continueProduct['type_label'] ?? null;
        $continueProgress = (int) ($continueProduct['progress'] ?? 0);

        // Commandes en attente de validation
        $pendingOrders = Order::query()
            ->with('items.course')
            ->where('user_id', $user->id)
            ->where('status', 'pending_manual')
            ->latest()
            ->get();

        return view('dashboard', compact(
            'dashboardStats',
            'learningProducts',
            'recentQuizAttempts',
            'continueUrl',
            'continueLabel',
            'continueMeta',
            'continueType',
            'continueProgress',
            'pendingOrders'
        ));
    }
}