<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use App\Models\ExerciseAttempt;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LearnerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalStudents     = User::role('student')->count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $quizAttempts30d   = QuizAttempt::where('started_at', '>=', now()->subDays(30))->count();
        $exerciseAttempts30d = ExerciseAttempt::where('started_at', '>=', now()->subDays(30))->count();
        $quizPassRate      = QuizAttempt::whereNotNull('submitted_at')->count() > 0
            ? round(
                QuizAttempt::whereNotNull('submitted_at')->where('is_passed', true)->count()
                / QuizAttempt::whereNotNull('submitted_at')->count() * 100
            )
            : 0;
        $completedExercises = ExerciseAttempt::where('is_completed', true)->count();

        return [
            Stat::make('Apprenants inscrits', number_format($totalStudents))
                ->description('Total des étudiants')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Inscriptions actives', number_format($activeEnrollments))
                ->description('Accès cours actifs')
                ->color('success')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Quiz (30 j)', number_format($quizAttempts30d))
                ->description('Tentatives quiz sur 30 jours')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Taux de réussite quiz', $quizPassRate . '%')
                ->description('Sur toutes les tentatives soumises')
                ->color($quizPassRate >= 60 ? 'success' : ($quizPassRate >= 40 ? 'warning' : 'danger'))
                ->icon('heroicon-o-check-badge'),

            Stat::make('Exercices (30 j)', number_format($exerciseAttempts30d))
                ->description('Tentatives exercices sur 30 jours')
                ->color('primary')
                ->icon('heroicon-o-pencil-square'),

            Stat::make('Exercices complétés', number_format($completedExercises))
                ->description('Total exercices terminés')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
