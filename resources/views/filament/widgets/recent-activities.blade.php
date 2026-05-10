@php $unread = $this->getUnreadCount(); @endphp
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-weight:700;">Activités récentes</span>
                @if($unread > 0)
                    <span style="
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        min-width:22px;
                        height:22px;
                        padding:0 8px;
                        border-radius:9999px;
                        background:#ef4444;
                        color:#fff;
                        font-size:11px;
                        font-weight:800;
                        line-height:1;
                    ">
                        {{ $unread }}
                    </span>
                @endif
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                {{-- Filtre date --}}
                <select wire:model.live="dateFilter" style="font-size:12px;padding:4px 8px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#d1d5db;cursor:pointer;">
                    <option value="all">Toutes</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>
                @if($unread > 0)
                    <x-filament::button wire:click="markAllRead" size="sm" color="gray" outlined>
                        Tout marquer comme lu
                    </x-filament::button>
                @endif
                <a href="{{ url('/admin/orders?tableFilters[status][value]=pending_manual') }}"
                   style="font-size:12px;font-weight:700;color:#f59e0b;text-decoration:none;padding:4px 10px;border:1px solid rgba(245,158,11,.4);border-radius:8px;">
                    Commandes en attente
                </a>
            </div>
        </x-slot>

        <style>
            .za-notif-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .za-notif-item {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 14px;
                border-radius: 14px;
                text-decoration: none;
                transition: .18s ease;
                border: 1px solid rgba(255,255,255,.06);
                background: rgba(255,255,255,.02);
            }

            .za-notif-item:hover {
                transform: translateY(-1px);
                border-color: rgba(245, 158, 11, .28);
                background: rgba(255,255,255,.035);
            }

            .za-notif-item.unread {
                border-left: 4px solid #f59e0b;
                background: rgba(245, 158, 11, .08);
            }

            .za-notif-icon {
                width: 42px;
                height: 42px;
                border-radius: 9999px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 18px;
                background: rgba(255,255,255,.9);
                box-shadow: 0 6px 18px rgba(0,0,0,.12);
            }

            .za-notif-body {
                flex: 1;
                min-width: 0;
            }

            .za-notif-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 4px;
            }

            .za-notif-title-row {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .za-notif-title {
                margin: 0;
                font-size: 14px;
                font-weight: 700;
                color: #f9fafb;
                line-height: 1.2;
            }

            .za-notif-dot {
                width: 8px;
                height: 8px;
                border-radius: 9999px;
                flex-shrink: 0;
            }

            .za-notif-tag {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                padding: 3px 9px;
                font-size: 10px;
                font-weight: 800;
                letter-spacing: .2px;
            }

            .za-notif-time {
                font-size: 11px;
                color: #9ca3af;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .za-notif-msg {
                margin: 0;
                font-size: 13px;
                color: #cbd5e1;
                line-height: 1.45;
            }

            .za-notif-actions {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-top: 8px;
                flex-wrap: wrap;
            }

            .za-notif-link {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: 12px;
                font-weight: 700;
                text-decoration: none;
                color: #f59e0b;
            }

            .za-notif-link:hover {
                text-decoration: underline;
            }

            .za-notif-read-btn {
                background: transparent;
                border: none;
                padding: 0;
                margin: 0;
                cursor: pointer;
                font-size: 12px;
                font-weight: 700;
                color: #94a3b8;
            }

            .za-notif-read-btn:hover {
                color: #ffffff;
                text-decoration: underline;
            }

            .za-notif-empty {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 34px 0;
                color: #94a3b8;
                text-align: center;
            }
        </style>

        @php
            $data     = $this->getActivities();
            $unreadList  = $data['unread'];
            $historyList = $data['history'];

            $cfgFor = fn($type) => match($type) {
                'new_user'             => ['dot'=>'#3b82f6','tag'=>'Nouvel utilisateur','tagBg'=>'rgba(59,130,246,.14)','tagColor'=>'#93c5fd'],
                'order_paid'           => ['dot'=>'#22c55e','tag'=>'Paiement reçu','tagBg'=>'rgba(34,197,94,.14)','tagColor'=>'#86efac'],
                'manual_order_pending' => ['dot'=>'#f59e0b','tag'=>'Accès manuel','tagBg'=>'rgba(245,158,11,.14)','tagColor'=>'#fcd34d'],
                'regime_order_pending' => ['dot'=>'#f59e0b','tag'=>'Régime','tagBg'=>'rgba(245,158,11,.14)','tagColor'=>'#fcd34d'],
                'order_approved'       => ['dot'=>'#10b981','tag'=>'Approuvée','tagBg'=>'rgba(16,185,129,.14)','tagColor'=>'#6ee7b7'],
                'order_refunded'       => ['dot'=>'#f97316','tag'=>'Remboursée','tagBg'=>'rgba(249,115,22,.14)','tagColor'=>'#fdba74'],
                default                => ['dot'=>'#94a3b8','tag'=>'Notification','tagBg'=>'rgba(148,163,184,.14)','tagColor'=>'#cbd5e1'],
            };
        @endphp

        @if($unreadList->isEmpty() && $historyList->isEmpty())
            <div class="za-notif-empty">
                <div style="font-size:30px;">🔔</div>
                <div style="font-size:13px;">Aucune activité pour cette période.</div>
            </div>
        @else

            {{-- ── NOTIFICATIONS NON LUES ── --}}
            @if($unreadList->isNotEmpty())
                <div class="za-notif-list">
                    @foreach($unreadList as $act)
                        @php $cfg = $cfgFor($act['type'] ?? ''); @endphp
                        <div class="za-notif-item unread">
                            <div class="za-notif-icon">{{ $act['icon'] }}</div>
                            <div class="za-notif-body">
                                <div class="za-notif-top">
                                    <div class="za-notif-title-row">
                                        <p class="za-notif-title">{{ $act['title'] }}</p>
                                        <span class="za-notif-dot" style="background:{{ $cfg['dot'] }}"></span>
                                        <span class="za-notif-tag" style="background:{{ $cfg['tagBg'] }};color:{{ $cfg['tagColor'] }};">{{ $cfg['tag'] }}</span>
                                    </div>
                                    <div class="za-notif-time">{{ $act['created_at']?->diffForHumans() }}</div>
                                </div>
                                <p class="za-notif-msg">{{ $act['message'] }}</p>
                                <div class="za-notif-actions">
                                    @if($act['url'])
                                        <a href="{{ $act['url'] }}" class="za-notif-link">Ouvrir →</a>
                                    @endif
                                    <button type="button" class="za-notif-read-btn" wire:click="markOneRead('{{ $act['id'] }}')">
                                        Marquer comme lu
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:10px 0 4px;font-size:12px;color:#6b7280;text-align:center;">
                    ✅ Aucune notification non lue.
                </div>
            @endif

            {{-- ── HISTORIQUE (lues) ── --}}
            @if($historyList->isNotEmpty())
                <details style="margin-top:14px;">
                    <summary style="cursor:pointer;font-size:12px;font-weight:700;color:#9ca3af;padding:6px 4px;list-style:none;display:flex;align-items:center;gap:6px;user-select:none;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:.2s;flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                        Historique ({{ $historyList->count() }} lues)
                    </summary>
                    <div class="za-notif-list" style="margin-top:8px;opacity:.7;">
                        @foreach($historyList as $act)
                            @php $cfg = $cfgFor($act['type'] ?? ''); @endphp
                            <div class="za-notif-item">
                                <div class="za-notif-icon" style="opacity:.6;">{{ $act['icon'] }}</div>
                                <div class="za-notif-body">
                                    <div class="za-notif-top">
                                        <div class="za-notif-title-row">
                                            <p class="za-notif-title" style="color:#9ca3af;">{{ $act['title'] }}</p>
                                            <span class="za-notif-tag" style="background:{{ $cfg['tagBg'] }};color:{{ $cfg['tagColor'] }};opacity:.7;">{{ $cfg['tag'] }}</span>
                                        </div>
                                        <div class="za-notif-time">{{ $act['created_at']?->diffForHumans() }}</div>
                                    </div>
                                    <p class="za-notif-msg" style="color:#6b7280;">{{ $act['message'] }}</p>
                                    @if($act['url'])
                                        <div class="za-notif-actions">
                                            <a href="{{ $act['url'] }}" class="za-notif-link" style="opacity:.7;">Ouvrir →</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif

        @endif
    </x-filament::section>
</x-filament-widgets::widget>