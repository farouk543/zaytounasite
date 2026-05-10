<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CoursePackItem;
use App\Models\User;

class PackUnlockService
{
    public function isUnlocked(User $user, Course $pack, CoursePackItem $item): bool
    {
        if (! $item->is_required) {
            return true;
        }

        $orderedItems = $pack->packItems()
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->get();

        $index = $orderedItems->search(fn ($row) => (int) $row->id === (int) $item->id);

        if ($index === false) {
            return true;
        }

        if ($index === 0) {
            return true;
        }

        $previousRequiredItem = $orderedItems[$index - 1];

        $progress = $previousRequiredItem->progressRecords()
            ->where('user_id', $user->id)
            ->first();

        return (bool) ($progress?->is_completed);
    }

    public function getLockMessage(User $user, Course $pack, CoursePackItem $item): ?string
    {
        if ($this->isUnlocked($user, $pack, $item)) {
            return null;
        }

        return 'Terminez l’étape obligatoire précédente pour débloquer cet élément.';
    }
}