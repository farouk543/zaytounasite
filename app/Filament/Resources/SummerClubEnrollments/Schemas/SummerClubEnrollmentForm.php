<?php

namespace App\Filament\Resources\SummerClubEnrollments\Schemas;

use App\Models\SummerClubSubscriptionRequest;
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

                    Forms\Components\Select::make('pack_key')
                        ->label('Pack')
                        ->options(SummerClubSubscriptionRequest::packOptions())
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, $set) {
                            $pack = SummerClubSubscriptionRequest::packDefinitions()[$state] ?? null;

                            $set('pack_name', $pack['name'] ?? null);

                            if (($pack['subjects'] ?? null) !== null) {
                                $set('selected_subjects', $pack['subjects']);
                            } else {
                                $set('selected_subjects', []);
                            }
                        }),

                    Forms\Components\TextInput::make('pack_name')
                        ->label('Nom du pack')
                        ->disabled()
                        ->dehydrated()
                        ->maxLength(255),

                    Forms\Components\Select::make('level')
                        ->label('Niveau autorisé')
                        ->options(SummerClubSubscriptionRequest::levelOptions())
                        ->helperText('Recommandé pour limiter l’accès au niveau exact de l’étudiant.')
                        ->searchable()
                        ->nullable(),

                    Forms\Components\CheckboxList::make('selected_subjects')
                        ->label('Matières autorisées')
                        ->options(SummerClubSubscriptionRequest::subjectOptions())
                        ->columns(3)
                        ->required()
                        ->helperText('Le pack complet force Français, Anglais et Mathématiques.')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options(SummerClubSubscriptionRequest::statusOptions())
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
