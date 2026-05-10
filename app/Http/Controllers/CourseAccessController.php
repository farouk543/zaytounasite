<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CoursePackItem;
use App\Models\Enrollment;
use App\Services\PackProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseAccessController extends Controller
{
    public function buy(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);
        abort_unless($course->is_paid, 400);

        // Ajouter au panier et rediriger vers la page de paiement
        $cart = session('cart.items', []);
        $cart[(string) $course->id] = 1;
        session(['cart.items' => $cart]);

        return redirect()->route('cart.show')
            ->with('success', '« ' . ($course->title_display ?? $course->title) . ' » ajouté au panier.');
    }

    public function access(
    Request $request,
    Course $course,
    \App\Services\PackProgressService $packProgressService,
    \App\Services\PackUnlockService $packUnlockService
) {
    abort_unless($course->is_published, 404);

    $user = $request->user();

    $hasFullAccess = ! $course->is_paid || ($user && $user->hasActiveEnrollmentForCourse($course->id));
    $hasPreviewAccess = $course->isPack() && (bool) $course->has_preview;

    // Si cours simple inclus dans un pack, vérifier accès via pack
    if (! $hasFullAccess && $course->isSimpleCourse() && $user) {
        $packIds = CoursePackItem::where('linked_course_id', $course->id)->pluck('pack_id');

        foreach ($packIds as $packId) {
            if ($user->hasActiveEnrollmentForCourse($packId)) {
                $hasFullAccess = true;
                break;
            }
        }
    }

    abort_unless($hasFullAccess || $hasPreviewAccess, 403);

    $course->load([
        'subject.level.track',
        'packItems.linkedCourse.subject.level.track',
        'packItems.quiz',
        'packItems.exercise',
        'packItems.progressRecords' => fn ($q) => $user ? $q->where('user_id', $user->id) : $q->whereRaw('1=0'),
        'quizzes',
        'exercises',
    ]);

    $packProgress = null;

    if ($course->isPack() && $user && $hasFullAccess) {
        $packProgress = $packProgressService->getPackProgressSummary($user, $course);
    }

    $packUnlockMap = [];

    if ($course->isPack()) {
        foreach ($course->packItems as $item) {
            if ($hasFullAccess && $user) {
                $packUnlockMap[$item->id] = [
                    'is_unlocked' => $packUnlockService->isUnlocked($user, $course, $item),
                    'message'     => $packUnlockService->getLockMessage($user, $course, $item),
                ];
            } else {
                $packUnlockMap[$item->id] = [
                    'is_unlocked' => false,
                    'message'     => 'Disponible après achat du pack.',
                ];
            }
        }
    }

    $courseQuizzes   = $course->isSimpleCourse() ? $course->quizzes->where('is_published', true) : collect();
    $courseExercises = $course->isSimpleCourse() ? $course->exercises->where('is_published', true) : collect();

    return view('courses.access', [
        'course' => $course,
        'packProgress' => $packProgress,
        'packUnlockMap' => $packUnlockMap,
        'courseQuizzes' => $courseQuizzes,
        'courseExercises' => $courseExercises,
        'hasFullAccess' => $hasFullAccess,
        'hasPreviewAccess' => $hasPreviewAccess,
    ]);
}

    public function pdf(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();

        $allowed = ! $course->is_paid || $user->hasActiveEnrollmentForCourse($course->id);

        if (! $allowed && $course->isSimpleCourse()) {
            $packIds = CoursePackItem::where('linked_course_id', $course->id)->pluck('pack_id');
            foreach ($packIds as $packId) {
                if ($user->hasActiveEnrollmentForCourse($packId)) {
                    $allowed = true;
                    break;
                }
            }
        }

        abort_unless($allowed, 403);

        abort_unless($course->isSimpleCourse(), 404);
        abort_unless($course->content_path, 404);

        $path = $course->content_path;

        abort_unless(Storage::disk('private')->exists($path), 404);

        return response()->file(Storage::disk('private')->path($path), [
            'Content-Type'              => 'application/pdf',
            // inline = affichage dans le viewer, pas de téléchargement automatique
            'Content-Disposition'       => 'inline; filename="document.pdf"',
            // Aucun cache → le fichier ne reste pas sur le disque local
            'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0, private',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
            // IE/Edge legacy : désactive l'option "Ouvrir" (forcer inline)
            'X-Download-Options'        => 'noopen',
            // Empêche le sniffing de type MIME
            'X-Content-Type-Options'    => 'nosniff',
            // Interdit l'embarquement dans des iframes d'autres domaines
            'X-Frame-Options'           => 'SAMEORIGIN',
        ]);
    }
}