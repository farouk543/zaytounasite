<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
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
            CreateAction::make()->label('Ajouter un témoignage'),
        ];
    }
}
