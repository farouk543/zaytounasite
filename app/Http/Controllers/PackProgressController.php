<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CoursePackItem;
use App\Services\PackProgressService;
use Illuminate\Http\Request;

class PackProgressController extends Controller
{
    public function start(Request $request, Course $course, CoursePackItem $item, PackProgressService $service)
    {
        abort_unless($course->id === $item->pack_id, 404);

        $user = $request->user();
        abort_unless(
            !$course->is_paid || $user->hasActiveEnrollmentForCourse($course->id),
            403
        );

        $service->startItem($user, $course, $item);

        return back();
    }

    public function complete(Request $request, Course $course, CoursePackItem $item, PackProgressService $service)
    {
        abort_unless($course->id === $item->pack_id, 404);

        $user = $request->user();
        abort_unless(
            !$course->is_paid || $user->hasActiveEnrollmentForCourse($course->id),
            403
        );

        $service->completeItem($user, $course, $item);

        return back();
    }
}