<?php

namespace App\Filament\Pages;

use App\Models\Enrollment;
use App\Models\ExerciseAttempt;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class LearnerReports extends Page
{
    protected string $view = 'filament.pages.learner-reports';

    protected static ?string $navigationLabel = 'Rapports apprenants';

    protected static string|\UnitEnum|null $navigationGroup = 'Pédagogie';

    protected static ?string $title = 'Rapports apprenants';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 20;

    public string $search = '';

    protected $queryString = ['search'];

    public function getStudents(): Collection
    {
        $query = User::role('student')
            ->with(['enrollments'])
            ->withCount([
                'enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'active'),
            ]);

        if (filled($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $students = $query->orderBy('name')->get();

        // Aggregate quiz/exercise stats per student
        $userIds = $students->pluck('id');

        $quizStats = QuizAttempt::whereIn('user_id', $userIds)
            ->whereNotNull('submitted_at')
            ->selectRaw('user_id, COUNT(*) as total, SUM(is_passed) as passed, AVG(percentage) as avg_pct')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $exStats = ExerciseAttempt::whereIn('user_id', $userIds)
            ->where('is_completed', true)
            ->selectRaw('user_id, COUNT(*) as total, AVG(score * 100.0 / NULLIF(max_score, 0)) as avg_pct')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return $students->map(function ($student) use ($quizStats, $exStats) {
            $quiz = $quizStats->get($student->id);
            $ex   = $exStats->get($student->id);

            return [
                'id'                  => $student->id,
                'name'                => $student->name,
                'email'               => $student->email,
                'created_at'          => $student->created_at,
                'active_enrollments'  => $student->active_enrollments_count,
                'quiz_attempts'       => $quiz ? (int) $quiz->total : 0,
                'quiz_passed'         => $quiz ? (int) $quiz->passed : 0,
                'quiz_avg_pct'        => $quiz ? round((float) $quiz->avg_pct) : null,
                'exercise_attempts'   => $ex ? (int) $ex->total : 0,
                'exercise_avg_pct'    => $ex ? round((float) $ex->avg_pct) : null,
            ];
        });
    }
}
