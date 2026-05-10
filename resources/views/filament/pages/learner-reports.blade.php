<x-filament-panels::page>
    @php
        $students = $this->getStudents();

        $totalStudents = $students->count();
        $activeStudents = $students->where('active_enrollments', '>', 0)->count();
        $quizParticipants = $students->where('quiz_attempts', '>', 0)->count();

        $avgQuiz = round(
            $students->whereNotNull('quiz_avg_pct')->avg('quiz_avg_pct') ?? 0
        );

        $avgExercise = round(
            $students->whereNotNull('exercise_avg_pct')->avg('exercise_avg_pct') ?? 0
        );
    @endphp

    <style>
        .za-lr-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .za-lr-search-card,
        .za-lr-table-card,
        .za-lr-stat {
            border: 1px solid rgba(255,255,255,.06);
            background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.015));
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,.16);
        }

        .za-lr-search-card {
            padding: 1rem;
        }

        .za-lr-stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1rem;
        }

        .za-lr-stat {
            padding: 1rem 1rem .95rem;
        }

        .za-lr-stat-label {
            font-size: .78rem;
            color: #9ca3af;
            margin-bottom: .35rem;
        }

        .za-lr-stat-value {
            font-size: 1.55rem;
            line-height: 1;
            font-weight: 800;
            color: #fff;
        }

        .za-lr-table-card {
            overflow: hidden;
        }

        .za-lr-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .za-lr-head-title {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
        }

        .za-lr-head-sub {
            font-size: .8rem;
            color: #9ca3af;
            margin-top: .18rem;
        }

        .za-lr-table-wrap {
            overflow-x: auto;
            padding: .4rem 1rem 1rem;
        }

        .za-lr-table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .za-lr-table thead th {
            text-align: left;
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9ca3af;
            font-weight: 700;
            padding: 0 .9rem .25rem;
            white-space: nowrap;
        }

        .za-lr-table thead th.center,
        .za-lr-table td.center {
            text-align: center;
        }

        .za-lr-row {
            background: rgba(255,255,255,.025);
            transition: .18s ease;
        }

        .za-lr-row:hover {
            background: rgba(255,255,255,.045);
            transform: translateY(-1px);
        }

        .za-lr-row td {
            padding: 1rem .9rem;
            vertical-align: middle;
            border-top: 1px solid rgba(255,255,255,.04);
            border-bottom: 1px solid rgba(255,255,255,.04);
        }

        .za-lr-row td:first-child {
            border-left: 1px solid rgba(255,255,255,.04);
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }

        .za-lr-row td:last-child {
            border-right: 1px solid rgba(255,255,255,.04);
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        .za-lr-student {
            display: flex;
            align-items: center;
            gap: .85rem;
            min-width: 260px;
        }

        .za-lr-avatar {
            width: 42px;
            height: 42px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            font-weight: 800;
            color: #111827;
            background: linear-gradient(135deg, #fbbf24, #fde68a);
            flex-shrink: 0;
        }

        .za-lr-name {
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            margin-bottom: .15rem;
        }

        .za-lr-email,
        .za-lr-meta {
            font-size: .76rem;
            color: #9ca3af;
            line-height: 1.35;
        }

        .za-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            padding: .28rem .58rem;
            border-radius: 9999px;
            font-size: .76rem;
            font-weight: 700;
            line-height: 1;
        }

        .za-pill-success {
            background: rgba(34,197,94,.14);
            color: #86efac;
        }

        .za-pill-warning {
            background: rgba(245,158,11,.14);
            color: #fcd34d;
        }

        .za-pill-danger {
            background: rgba(239,68,68,.14);
            color: #fca5a5;
        }

        .za-pill-muted {
            background: rgba(148,163,184,.14);
            color: #cbd5e1;
        }

        .za-score-good { color: #86efac; font-weight: 800; }
        .za-score-mid  { color: #fcd34d; font-weight: 800; }
        .za-score-bad  { color: #fca5a5; font-weight: 800; }

        .za-lr-foot {
            padding: 0 1.25rem 1rem;
            font-size: .78rem;
            color: #9ca3af;
        }

        .za-lr-empty {
            padding: 3rem 1rem;
            text-align: center;
            color: #9ca3af;
            font-size: .92rem;
        }

        @media (max-width: 1200px) {
            .za-lr-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .za-lr-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="za-lr-shell">
        <div>
            <h1 style="font-size:2rem; font-weight:800; color:#fff; margin:0;">Rapports apprenants</h1>
            <p style="margin:.35rem 0 0; color:#9ca3af; font-size:.92rem;">
                Vue claire des performances, participations et inscriptions récentes.
            </p>
        </div>

        <div class="za-lr-search-card">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Rechercher par nom ou email..."
                />
            </x-filament::input.wrapper>
        </div>

        <div class="za-lr-stats">
            <div class="za-lr-stat">
                <div class="za-lr-stat-label">Apprenants affichés</div>
                <div class="za-lr-stat-value">{{ $totalStudents }}</div>
            </div>

            <div class="za-lr-stat">
                <div class="za-lr-stat-label">Avec cours actifs</div>
                <div class="za-lr-stat-value">{{ $activeStudents }}</div>
            </div>

            <div class="za-lr-stat">
                <div class="za-lr-stat-label">Ont tenté un quiz</div>
                <div class="za-lr-stat-value">{{ $quizParticipants }}</div>
            </div>

            <div class="za-lr-stat">
                <div class="za-lr-stat-label">Moyenne quiz</div>
                <div class="za-lr-stat-value">{{ $avgQuiz }}%</div>
            </div>

            <div class="za-lr-stat">
                <div class="za-lr-stat-label">Moyenne exercices</div>
                <div class="za-lr-stat-value">{{ $avgExercise }}%</div>
            </div>
        </div>

        @if($students->isEmpty())
            <div class="za-lr-table-card">
                <div class="za-lr-empty">Aucun apprenant trouvé.</div>
            </div>
        @else
            <div class="za-lr-table-card">
                <div class="za-lr-head">
                    <div>
                        <div class="za-lr-head-title">Liste des apprenants</div>
                        <div class="za-lr-head-sub">Suivi rapide des cours, quiz et exercices</div>
                    </div>
                </div>

                <div class="za-lr-table-wrap">
                    <table class="za-lr-table">
                        <thead>
                            <tr>
                                <th>Apprenant</th>
                                <th class="center">Cours actifs</th>
                                <th class="center">Quiz tentés</th>
                                <th class="center">Quiz réussis</th>
                                <th class="center">Moy. quiz</th>
                                <th class="center">Exercices</th>
                                <th class="center">Moy. exercices</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $s)
                                @php
                                    $initial = strtoupper(mb_substr($s['name'] ?? 'A', 0, 1));

                                    $quizClass = $s['quiz_avg_pct'] !== null
                                        ? ($s['quiz_avg_pct'] >= 70 ? 'za-score-good' : ($s['quiz_avg_pct'] >= 50 ? 'za-score-mid' : 'za-score-bad'))
                                        : '';

                                    $exerciseClass = $s['exercise_avg_pct'] !== null
                                        ? ($s['exercise_avg_pct'] >= 70 ? 'za-score-good' : ($s['exercise_avg_pct'] >= 50 ? 'za-score-mid' : 'za-score-bad'))
                                        : '';

                                    $passPillClass = 'za-pill-warning';
                                    if (($s['quiz_attempts'] ?? 0) > 0 && ($s['quiz_passed'] ?? 0) === ($s['quiz_attempts'] ?? 0)) {
                                        $passPillClass = 'za-pill-success';
                                    }
                                @endphp

                                <tr class="za-lr-row">
                                    <td>
                                        <div class="za-lr-student">
                                            <div class="za-lr-avatar">{{ $initial }}</div>
                                            <div>
                                                <div class="za-lr-name">{{ $s['name'] }}</div>
                                                <div class="za-lr-email">{{ $s['email'] }}</div>
                                                <div class="za-lr-meta">
                                                    inscrit {{ \Carbon\Carbon::parse($s['created_at'])->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="center">
                                        @if($s['active_enrollments'] > 0)
                                            <span class="za-pill za-pill-success">{{ $s['active_enrollments'] }}</span>
                                        @else
                                            <span class="za-pill za-pill-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="center">
                                        @if($s['quiz_attempts'] > 0)
                                            <span class="za-pill za-pill-muted">{{ $s['quiz_attempts'] }}</span>
                                        @else
                                            <span class="za-pill za-pill-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="center">
                                        @if($s['quiz_attempts'] > 0)
                                            <span class="za-pill {{ $passPillClass }}">
                                                {{ $s['quiz_passed'] }} / {{ $s['quiz_attempts'] }}
                                            </span>
                                        @else
                                            <span class="za-pill za-pill-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="center">
                                        @if($s['quiz_avg_pct'] !== null)
                                            <span class="{{ $quizClass }}">{{ $s['quiz_avg_pct'] }}%</span>
                                        @else
                                            <span class="za-pill za-pill-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="center">
                                        @if($s['exercise_attempts'] > 0)
                                            <span class="za-pill za-pill-muted">{{ $s['exercise_attempts'] }}</span>
                                        @else
                                            <span class="za-pill za-pill-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="center">
                                        @if($s['exercise_avg_pct'] !== null)
                                            <span class="{{ $exerciseClass }}">{{ $s['exercise_avg_pct'] }}%</span>
                                        @else
                                            <span class="za-pill za-pill-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="za-lr-foot">
                    {{ $students->count() }} apprenant(s) affiché(s)
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>