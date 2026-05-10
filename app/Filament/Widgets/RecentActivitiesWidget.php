<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Notifications\DatabaseNotification;

class RecentActivitiesWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activities';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public string $dateFilter = 'all';

    public function getActivities(): array
    {
        $adminId = auth()->id();

        $query = DatabaseNotification::query()
            ->where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $adminId)
            ->orderByDesc('created_at');

        $query = match($this->dateFilter) {
            'today'  => $query->whereDate('created_at', today()),
            'week'   => $query->where('created_at', '>=', now()->startOfWeek()),
            'month'  => $query->where('created_at', '>=', now()->startOfMonth()),
            default  => $query,
        };

        $all = $query->limit(50)->get()->map(fn ($n) => [
            'id'         => $n->id,
            'read'       => ! is_null($n->read_at),
            'icon'       => $n->data['icon']    ?? '🔔',
            'title'      => $n->data['title']   ?? '',
            'message'    => $n->data['message'] ?? '',
            'url'        => $n->data['url']     ?? null,
            'type'       => $n->data['type']    ?? 'info',
            'created_at' => $n->created_at,
        ]);

        return [
            'unread'  => $all->where('read', false)->take(4)->values(),
            'history' => $all->where('read', true)->take(10)->values(),
        ];
    }

    public function getUnreadCount(): int
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function markAllRead(): void
    {
        DatabaseNotification::query()
            ->where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markOneRead(string $id): void
    {
        DatabaseNotification::query()
            ->where('id', $id)
            ->where('notifiable_id', auth()->id())
            ->update(['read_at' => now()]);
    }
}
