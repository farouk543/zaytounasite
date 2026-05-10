<?php

namespace App\Filament\Resources\SummerClubEnrollments\Schemas;

use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SummerClubEnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Abonnement Club d’été')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Élève')
                        ->options(fn () => User::query()->orderBy('name')->pluck('email', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('pack_name')
                        ->label('Pack')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('pack_key')
                        ->label('Clé pack')
                        ->maxLength(255),

                    Forms\Components\CheckboxList::make('selected_subjects')
                        ->label('Matières')
                        ->options([
                            'Français' => 'Français',
                            'Anglais' => 'Anglais',
                            'Mathématiques' => 'Mathématiques',
                        ])
                        ->columns(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending' => 'En attente',
                            'active' => 'Actif',
                            'canceled' => 'Annulé',
                            'expired' => 'Expiré',
                        ])
                        ->default('pending')
                        ->required(),

                    Forms\Components\Select::make('confirmed_by')
                        ->label('Confirmé par')
                        ->options(fn () => User::query()->orderBy('name')->pluck('email', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Début')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Expiration')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('confirmed_at')
                        ->label('Confirmé le')
                        ->nullable(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
