Dans le module Club d’été, je veux rendre l’accès plus strict et plus clair.

Partie 1 — Accès via SUMMER_CLUB_COURSE_ID

Dans `App\Http\Controllers\Student\SummerClubController`, privilégier `SUMMER_CLUB_COURSE_ID` comme source principale pour identifier le Course Club d’été.

Règle :
- Si `SUMMER_CLUB_COURSE_ID` est défini dans `.env`, utiliser uniquement ce course_id pour vérifier l’enrollment actif.
- Si `SUMMER_CLUB_COURSE_ID` n’est pas défini, garder le fallback existant par slug/titre.
- Ajouter un commentaire clair expliquant que c’est recommandé en production parce que les packs peuvent évoluer mais l’accès final doit pointer vers un Course précis.

La vérification doit rester :
- user connecté
- enrollment.status = active
- enrollment lié au course_id du Club d’été
- enrollment non expiré si une date d’expiration existe

Ne pas modifier la logique de paiement existante.

Partie 2 — Clic sur catalogue public verrouillé

Sur la page publique `/club-ete`, dans la section catalogue verrouillé :
- Les cartes restent non accessibles.
- Quand un visiteur clique sur une carte du catalogue, il faut faire un scroll fluide vers la section des packs/abonnement déjà présente plus bas dans la page.
- Afficher un petit message/toast doux :
  “Choisissez un pack pour débloquer ce contenu.”

À faire :
1. Identifier la section des packs en bas de la page.
2. Ajouter un id HTML clair sur cette section, par exemple :
   `id="summer-club-packs"`
3. Modifier les cartes du catalogue pour qu’au clic elles appellent un scroll vers `#summer-club-packs`.
4. Si un ancien message au clic existait, le remplacer ou l’adapter avec :
   “Choisissez un pack pour débloquer ce contenu.”
5. Ajouter une petite animation visuelle sur la section des packs après le scroll :
   - léger highlight
   - ou pulse doux pendant 1 seconde
6. Garder le design responsive.
7. Ne pas rendre les cartes cliquables vers les quiz/exercices.
8. Vérifier :
   - php -l
   - php artisan route:list --path=club-ete
   - npm run build<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summer_club_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('subject')->nullable();
            $table->string('level')->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_locked')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('type');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summer_club_resources');
    }
};
