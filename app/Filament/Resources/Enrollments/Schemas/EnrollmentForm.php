<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Accès au cours')
                ->description("L'achat crée l'accès automatiquement. Ici, l’admin peut gérer le statut et la durée d’accès.")
                ->columns(2)
                ->schema([
                    // ✅ affichage lisible (lecture seule)
                    Forms\Components\Placeholder::make('user_email')
                        ->label('Utilisateur (email)')
                        ->content(fn ($record) => $record?->user?->email ?? '—')
                        ->columnSpan(1),

                    Forms\Components\Placeholder::make('course_title')
                        ->label('Cours (titre)')
                        ->content(fn ($record) => $record?->course?->title ?? '—')
                        ->columnSpan(1),

                    // ✅ on garde les IDs pour référence, mais cachés
                    Forms\Components\Hidden::make('user_id'),
                    Forms\Components\Hidden::make('course_id'),

                    // ✅ admin modifie seulement ça
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'active' => 'Actif',
                            'canceled' => 'Suspendu',
                            'expired' => 'Expiré',
                        ])
                        ->required(),

                    Forms\Components\DateTimePicker::make('access_ends_at')
                        ->label("Fin d'accès (optionnel)")
                        ->helperText("Laisser vide pour un accès illimité.")
                        ->nullable(),

                    // ✅ lecture seule
                    Forms\Components\DateTimePicker::make('enrolled_at')
                        ->label('Acheté le')
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }
}