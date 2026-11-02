<?php

namespace App\Notifications;

use App\Services\Analytics\Funnel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Bilan marketing mensuel envoyé aux admins (e-mail uniquement) : voir MonthlyReport.
 */
class MonthlyMarketingReport extends Notification
{
    public function __construct(public array $report) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->report;
        $n = fn ($v, $d = 0) => $v === null ? '—' : number_format((float) $v, $d, ',', ' ');
        $site = $r['site'];
        $delta = $site['visitors_previous'] > 0
            ? ' ('.($site['visitors'] >= $site['visitors_previous'] ? '+' : '').round(($site['visitors'] - $site['visitors_previous']) / $site['visitors_previous'] * 100).' % vs mois précédent)'
            : '';

        $score = $r['score'];
        $mail = (new MailMessage)
            ->subject('Bilan de '.$r['label'].' — '.($score['score'] !== null ? $score['score'].'/100' : 'Zaytouna Academy'))
            ->greeting('Bilan marketing — '.$r['label'])
            ->line('**Note de la stratégie : '.($score['score'] !== null ? $score['score'].'/100' : '—').'** — '.$score['verdict'])
            ->line($score['summary']);

        $mail->line('### Site zaytouna-academy.net')
            ->line('• Visiteurs : **'.$n($site['visitors']).'**'.$delta)
            ->line('• Pages vues : '.$n($site['pageviews']).' · Inscriptions : '.$n($site['signups']))
            ->line('• Commandes : '.$n($site['orders']).' dont **'.$n($site['paid']).' payées / acceptées**'
                .($site['revenue'] ? ' — '.collect($site['revenue'])->map(fn ($v, $cur) => $n($v, 2).' '.$cur)->implode(' · ') : ''));

        if ($p = $r['platform']) {
            $mail->line('### Plateforme d’enseignement')
                ->line('• Élèves actifs : **'.$n($p['active_students'] ?? null).'** · nouveaux : '.$n($p['new_students'] ?? null))
                ->line('• Fidélisation : **'.$n($p['retention_rate'] ?? null, 1).' %** · assiduité : '.$n($p['attendance_rate'] ?? null, 1).' %')
                ->line('• Séances : '.$n($p['seances_held'] ?? null).' ('.$n($p['hours_taught'] ?? null, 1).' h) · note moyenne : '
                    .(($p['feedback_average'] ?? null) !== null ? $n($p['feedback_average'], 1).'/5' : '—'));
        } else {
            $mail->line('_Plateforme d’enseignement : chiffres indisponibles ce mois-ci._');
        }

        $t = $r['funnel']['total'];
        $stages = collect(Funnel::STAGES)->filter(fn ($label, $key) => $t[$key] !== null)
            ->map(fn ($label, $key) => $label.' '.$n($t[$key]))->implode(' → ');
        $mail->line('### Parcours complet')->line($stages);
        if ($leak = $r['funnel']['leak']) {
            $mail->line('Plus grosse perte : **'.$leak['from'].' → '.$leak['to'].' ('.$leak['rate'].' %)**.');
        }

        if ($r['goals']) {
            $mail->line('### Objectifs');
            foreach ($r['goals'] as $g) {
                $pct = $g['unit'] === '%' ? ' %' : '';
                $mail->line('• '.$g['label'].' ('.$g['scope'].') : '.$n($g['actual'], $pct ? 1 : 0).$pct.' / '.$n($g['target'], $pct ? 1 : 0).$pct.' — '.$g['status']);
            }
        }

        if ($r['actions']) {
            $mail->line('### Les 3 actions du mois');
            foreach ($r['actions'] as $i => $action) {
                $mail->line(($i + 1).'. '.$action);
            }
        }

        return $mail->action('Voir les statistiques détaillées', url('/admin/statistiques?goalMonth='.$r['month']));
    }
}
