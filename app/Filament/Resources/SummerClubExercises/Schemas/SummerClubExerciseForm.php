<?php

namespace App\Filament\Resources\SummerClubExercises\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SummerClubExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Exercice interactif')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('summer_club_resource_id')
                        ->label('Ressource liée')
                        ->relationship('resource', 'title')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Permet de rattacher cet exercice à une formation ou ressource du Club d’été.')
                        ->columnSpanFull(),

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

                    Forms\Components\FileUpload::make('cover_image_path')
                        ->label('Image de couverture')
                        ->disk('public')
                        ->directory('summer-club/exercise-covers')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('180')
                        ->openable()
                        ->downloadable()
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
