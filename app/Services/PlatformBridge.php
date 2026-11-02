<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Testimonial;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Échanges site → plateforme d'enseignement (même jeton partagé que les statistiques) :
 *  - alertes en direct dans la cloche des admins de la plateforme (POST /api/site/events) ;
 *  - création du compte élève depuis une demande « régime » (POST /api/site/students) ;
 *  - import des avis publics d'élèves comme témoignages à valider (GET /api/site/testimonials).
 */
class PlatformBridge
{
    /** Fuseau horaire par défaut de l'élève selon le régime (sert aux statistiques par pays de la plateforme). */
    public const REGIME_TIMEZONES = [
        'tunisia' => 'Africa/Tunis',
        'france' => 'Europe/Paris',
        'qatar' => 'Asia/Qatar',
        'saudi' => 'Asia/Riyadh',
    ];

    public const COUNTRY_TIMEZONES = [
        'TN' => 'Africa/Tunis', 'FR' => 'Europe/Paris', 'QA' => 'Asia/Qatar', 'SA' => 'Asia/Riyadh', 'CA' => 'America/Toronto',
    ];

    public function configured(): bool
    {
        return filled(config('zaytouna.platform_url')) && filled(config('zaytouna.platform_stats_token'));
    }

    private function http(int $timeout = 6): PendingRequest
    {
        return Http::withToken((string) config('zaytouna.platform_stats_token'))
            ->acceptJson()
            ->timeout($timeout)
            ->baseUrl(rtrim((string) config('zaytouna.platform_url'), '/').'/api/site');
    }

    /**
     * Relaie une notification admin du site vers la plateforme, SANS donnée personnelle :
     * type + titre « sûr » (jamais le nom, l'e-mail ou le message du parent).
     */
    public function pushNotification(array $data): void
    {
        if (! $this->configured() || empty($data['type'])) {
            return;
        }

        $type = (string) $data['type'];
        $title = match ($type) {
            'new_user' => 'Nouvel inscrit sur le site',
            'testimonial' => preg_match('/\((\d)\/5\)/', (string) ($data['title'] ?? ''), $m)
                ? "Nouvel avis de parent ({$m[1]}/5) à valider" : 'Nouvel avis de parent à valider',
            'manual_order' => 'Nouvelle commande à valider',
            default => (string) ($data['title'] ?? 'Nouvelle activité'),
        };

        try {
            $this->http(4)->post('/events', [
                'type' => $type,
                'icon' => mb_substr((string) ($data['icon'] ?? '🌐'), 0, 8),
                'title' => mb_substr($title, 0, 160),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Crée (ou relie) le compte élève de la plateforme pour une demande « régime ».
     *
     * @return array{ok: bool, created?: bool, login?: string, password?: string, reason?: string, error?: string}
     */
    public function createStudent(Order $order, string $name, string $email, ?string $timezone): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'error' => 'Plateforme non configurée (PLATFORM_URL / PLATFORM_STATS_TOKEN).'];
        }

        try {
            $response = $this->http(10)->post('/students', array_filter([
                'site_order_id' => $order->id,
                'name' => $name,
                'email' => $email,
                'timezone' => $timezone,
            ]));
        } catch (Throwable $e) {
            report($e);

            return ['ok' => false, 'error' => 'La plateforme ne répond pas. Réessayez dans un instant.'];
        }

        if ($response->status() === 409) {
            return ['ok' => false, 'reason' => 'email_taken',
                'error' => 'Cette adresse est déjà utilisée sur la plateforme par un professeur ou un admin : choisissez une autre adresse pour l’élève.'];
        }
        if ($response->status() === 422) {
            return ['ok' => false, 'error' => 'Données refusées par la plateforme : '.collect($response->json('errors', []))->flatten()->implode(' ')];
        }
        if (! $response->successful()) {
            return ['ok' => false, 'error' => 'La plateforme a répondu avec une erreur ('.$response->status().').'];
        }

        return ['ok' => true] + $response->json();
    }

    /** Importe les avis publics bien notés de la plateforme, non publiés (à valider). Renvoie le nombre d'ajouts. */
    public function importTestimonials(): ?int
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $response = $this->http(10)->get('/testimonials');
        } catch (Throwable $e) {
            report($e);

            return null;
        }
        if (! $response->successful()) {
            return null;
        }

        $added = 0;
        foreach ((array) $response->json('data', []) as $row) {
            $ref = 'platform:'.(int) ($row['id'] ?? 0);
            if (empty($row['comment']) || Testimonial::where('external_ref', $ref)->exists()) {
                continue;
            }

            Testimonial::create([
                'author_name' => mb_substr((string) ($row['author'] ?? 'Élève'), 0, 80),
                'author_role' => mb_substr('Élève'.(! empty($row['subject']) ? ' — '.$row['subject'] : ''), 0, 120),
                'country' => in_array($row['country'] ?? null, ['TN', 'FR', 'SA', 'QA', 'CA'], true) ? $row['country'] : null,
                'program' => 'general',
                'rating' => max(1, min(5, (int) ($row['rating'] ?? 5))),
                'quote' => (string) $row['comment'],
                'source' => 'plateforme',
                'is_published' => false, // toujours validé par un admin avant publication
                'received_at' => $row['date'] ?? now()->toDateString(),
            ])->forceFill(['external_ref' => $ref])->save();
            $added++;
        }

        return $added;
    }
}
