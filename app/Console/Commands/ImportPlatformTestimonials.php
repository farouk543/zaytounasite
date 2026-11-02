<?php

namespace App\Console\Commands;

use App\Services\PlatformBridge;
use Illuminate\Console\Command;

/** Importe (non publiés) les avis publics bien notés des élèves de la plateforme. Planifié chaque semaine. */
class ImportPlatformTestimonials extends Command
{
    protected $signature = 'testimonials:import-platform';

    protected $description = 'Importe les avis publics 4-5/5 des élèves de la plateforme comme témoignages à valider';

    public function handle(PlatformBridge $bridge): int
    {
        $added = $bridge->importTestimonials();

        if ($added === null) {
            $this->warn('Plateforme non configurée ou injoignable.');

            return self::SUCCESS;
        }

        $this->info("$added avis importé(s), à valider dans Témoignages.");

        return self::SUCCESS;
    }
}
