<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Services\PlatformBridge;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\HtmlString;

class ManageTestimonials extends ManageRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reviewLink')
                ->label('Lien à envoyer aux parents')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->modalHeading('Lien « Laisser un avis »')
                ->modalDescription('Envoyez ce lien par WhatsApp ou e-mail aux parents satisfaits (idéalement après 3 à 4 semaines de cours). Les avis reçus arrivent ici, non publiés, pour validation.')
                ->modalContent(fn () => new HtmlString(
                    '<input readonly onclick="this.select()" value="'.e(route('reviews.create')).'" '
                    .'style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;font-family:monospace">'
                ))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
            Action::make('importPlatform')
                ->label('Importer les avis de la plateforme')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => app(PlatformBridge::class)->configured())
                ->requiresConfirmation()
                ->modalDescription('Récupère les avis d’élèves notés 4 ou 5/5 que l’élève a rendus publics sur la plateforme. Ils arrivent ici NON publiés : relisez-les avant de les publier.')
                ->action(function () {
                    $added = app(PlatformBridge::class)->importTestimonials();

                    $added === null
                        ? Notification::make()->title('La plateforme ne répond pas')->danger()->send()
                        : Notification::make()->title($added > 0 ? "$added avis importé(s), à valider" : 'Aucun nouvel avis à importer')->success()->send();
                }),
            CreateAction::make()->label('Ajouter un témoignage'),
        ];
    }
}
