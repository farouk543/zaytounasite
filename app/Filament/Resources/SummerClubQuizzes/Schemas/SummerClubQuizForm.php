<?php

namespace App\Filament\Resources\SummerClubQuizzes\Schemas;

use App\Models\SummerClubResource;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SummerClubQuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Quiz interactif')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('summer_club_resource_id')
                        ->label('Ressource liée')
                        ->options(fn () => SummerClubResource::query()
                            ->where('type', 'quiz')
                            ->orderBy('sort_order')
                            ->orderBy('title')
                            ->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\Select::make('subject')
                        ->label('Matière')
                        ->options([
                            'Français' => 'Français',
                            'Anglais' => 'Anglais',
                            'Mathématiques' => 'Mathématiques',
                        ])
                        ->nullable(),

                    Forms\Components\TextInput::make('level')
                        ->label('Niveau scolaire')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié')
                        ->default(false),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }
}
