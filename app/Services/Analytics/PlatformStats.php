<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Statistiques agrégées de la plateforme d'enseignement (service.zaytouna-academy.net) :
 * élèves actifs, fidélisation, assiduité, note des séances, par pays. Aucune donnée personnelle.
 *
 * Source : GET {PLATFORM_URL}/api/marketing/stats?month=AAAA-MM (jeton PLATFORM_STATS_TOKEN).
 * Résultat mis en cache (30 min pour le mois en cours, 1 jour pour les mois passés) ;
 * null si l'API n'est pas configurée ou ne répond pas — les écrans restent utilisables.
 */
class PlatformStats
{
    /** @var array<string, ?array> */
    private array $memo = [];

    public function configured(): bool
    {
        return filled(config('zaytouna.platform_url')) && filled(config('zaytouna.platform_stats_token'));
    }

    /** @return array<string, mixed>|null */
    public function month(string $month): ?array
    {
        if (! $this->configured() || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        if (array_key_exists($month, $this->memo)) {
            return $this->memo[$month];
        }

        $current = $month >= now(config('marketing.timezone'))->format('Y-m');
        $key = "platform-stats:$month";

        if (($cached = Cache::get($key)) !== null) {
            return $this->memo[$month] = $cached;
        }

        try {
            $response = Http::withToken((string) config('zaytouna.platform_stats_token'))
                ->acceptJson()
                ->timeout(6)
                ->get(rtrim((string) config('zaytouna.platform_url'), '/').'/api/marketing/stats', ['month' => $month]);

            $data = $response->successful() ? $response->json() : null;
        } catch (Throwable $e) {
            report($e);
            $data = null;
        }

        if (is_array($data)) {
            Cache::put($key, $data, $current ? now()->addMinutes(30) : now()->addDay());
        }

        return $this->memo[$month] = is_array($data) ? $data : null;
    }

    /** Valeur d'un indicateur, au global ou pour un pays (élèves actifs / nouveaux / inscrits). */
    public function value(string $month, string $key, ?string $country = null): ?float
    {
        $data = $this->month($month);
        if ($data === null) {
            return null;
        }

        $value = $country ? data_get($data, "by_country.$country.$key") : ($data[$key] ?? null);

        return $value === null ? null : (float) $value;
    }
}
