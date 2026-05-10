<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CoursePackItem;
use App\Models\CoursePackItemProgress;
use App\Models\User;

class PackProgressService
{
    public function startItem(User $user, Course $pack, CoursePackItem $item): CoursePackItemProgress
    {
        return CoursePackItemProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'pack_id' => $pack->id,
                'course_pack_item_id' => $item->id,
            ],
            [
                'is_started' => true,
                'started_at' => now(),
                'last_seen_at' => now(),
                'progress_percent' => 5,
            ]
        );
    }

    public function completeItem(User $user, Course $pack, CoursePackItem $item): CoursePackItemProgress
    {
        return CoursePackItemProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'pack_id' => $pack->id,
                'course_pack_item_id' => $item->id,
            ],
            [
                'is_started' => true,
                'is_completed' => true,
                'started_at' => now(),
                'completed_at' => now(),
                'last_seen_at' => now(),
                'progress_percent' => 100,
            ]
        );
    }

    public function touchItem(User $user, Course $pack, CoursePackItem $item, int $progressPercent = 10): CoursePackItemProgress
    {
        $existing = CoursePackItemProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'pack_id' => $pack->id,
                'course_pack_item_id' => $item->id,
            ],
            [
                'is_started' => true,
                'started_at' => now(),
                'last_seen_at' => now(),
                'progress_percent' => $progressPercent,
            ]
        );

        if (! $existing->wasRecentlyCreated) {
            $existing->update([
                'is_started' => true,
                'last_seen_at' => now(),
                'progress_percent' => max((int) $existing->progress_percent, $progressPercent),
            ]);
        }

        return $existing->fresh();
    }

    public function getPackProgressSummary(User $user, Course $pack): array
    {
        $items = $pack->packItems()->where('is_required', true)->get();

        $total = $items->count();

        if ($total === 0) {
            return [
                'total_items' => 0,
                'completed_items' => 0,
                'started_items' => 0,
                'percentage' => 0,
            ];
        }

        $progress = CoursePackItemProgress::query()
            ->where('user_id', $user->id)
            ->where('pack_id', $pack->id)
            ->get()
            ->keyBy('course_pack_item_id');

        $completed = 0;
        $started = 0;

        foreach ($items as $item) {
            $row = $progress->get($item->id);

            if ($row?->is_started) {
                $started++;
            }

            if ($row?->is_completed) {
                $completed++;
            }
        }

        $percentage = (int) round(($completed / $total) * 100);

        return [
            'total_items' => $total,
            'completed_items' => $completed,
            'started_items' => $started,
            'percentage' => $percentage,
        ];
    }
}