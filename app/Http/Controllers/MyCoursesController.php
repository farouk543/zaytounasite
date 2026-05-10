<?php

namespace App\Http\Controllers;

use App\Services\PackProgressService;
use Illuminate\Http\Request;

class MyCoursesController extends Controller
{
    public function __construct(private PackProgressService $packProgress) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['course.subject.level.track', 'course.packItems'])
            ->where('status', 'active')
            ->latest('enrolled_at')
            ->get();

        // Compute pack progress for each pack course
        $packProgressMap = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->delivery_type === 'pack') {
                $packProgressMap[$course->id] = $this->packProgress->getPackProgressSummary($user, $course);
            }
        }

        return view('my-courses.index', compact('enrollments', 'packProgressMap'));
    }
}