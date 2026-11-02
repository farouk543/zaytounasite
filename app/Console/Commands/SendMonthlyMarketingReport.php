<?php

namespace App\Console\Commands;

use App\Notifications\MonthlyMarketingReport;
use App\Services\Analytics\MonthlyReport;
use App\Support\AdminNotifier;
use Illuminate\Console\Command;

/**
 * Envoie le bilan marketing d'un mois aux admins (+ ADMIN_NOTIFICATION_EMAILS).
 * Planifié le 1er de chaque mois à 8 h (heure de Tunis) pour le mois écoulé.
 */
class SendMonthlyMarketingReport extends Command
{
    protected $signature = 'marketing:monthly-report {--month= : Mois AAAA-MM (par défaut : le mois précédent)} {--to=* : Adresse(s) à la place des admins}';

    protected $description = 'Envoie par e-mail le bilan marketing mensuel (site + plateforme + parcours + 3 actions)';

    public function handle(MonthlyReport $builder): int
    {
        $month = (string) ($this->option('month') ?: MonthlyReport::previousMonth());
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('Mois invalide : utilisez AAAA-MM.');

            return self::FAILURE;
        }

        $notification = new MonthlyMarketingReport($builder->build($month));
        $recipients = collect($this->option('to'))->filter()->whenEmpty(fn () => AdminNotifier::recipients());

        $sent = $recipients->filter(fn ($email) => AdminNotifier::mailTo($email, $notification))->count();
        $this->info("Bilan de $month envoyé à $sent adresse(s) sur {$recipients->count()}.");

        return $sent > 0 || $recipients->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
