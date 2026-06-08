<?php

namespace App\Filament\Resources\SummerClubResources\Schemas;

use App\Models\SummerClubSubscriptionRequest;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SummerClubResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Contenu pédagogique')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options([
                            'exercice' => 'Exercice',
                            'quiz' => 'Quiz',
                            'fiche' => 'Fiche de révision',
                        ])
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('subject')
                        ->label('Matière')
                        ->options(SummerClubSubscriptionRequest::subjectOptions())
                        ->nullable(),

                    Forms\Components\Select::make('level')
                        ->label('Niveau scolaire')
                        ->options(SummerClubSubscriptionRequest::levelOptions())
                        ->in(array_keys(SummerClubSubscriptionRequest::levelOptions()))
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('content')
                        ->label('Contenu')
                        ->columnSpanFull()
                        ->visible(fn ($get) => in_array($get('type'), ['exercice', 'fiche'], true)),

                    Forms\Components\FileUpload::make('file_path')
                        ->label('Fichier PDF ou support')
                        ->disk('public')
                        ->directory('summer-club/resources')
                        ->visibility('public')
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('cover_image_path')
                        ->label('Image de couverture')
                        ->disk('public')
                        ->directory('summer-club/covers')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('180')
                        ->openable()
                        ->downloadable()
                        ->columnSpanFull(),
                ]),

            Section::make('Correction exercice')
                ->description('Ces éléments restent réservés à l’espace étudiant après confirmation de l’abonnement.')
                ->columns(2)
                ->visible(fn ($get) => $get('type') === 'exercice')
                ->schema([
                    Forms\Components\RichEditor::make('correction_content')
                        ->label('Correction')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('correction_file_path')
                        ->label('Fichier de correction')
                        ->disk('public')
                        ->directory('summer-club/corrections')
                        ->visibility('public')
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),
                ]),

            Section::make('Publication et accès')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié')
                        ->default(false),

                    Forms\Components\Toggle::make('is_locked')
                        ->label('Verrouillé')
                        ->default(true),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Afficher sur la page Club d’été')
                        ->helperText('Seules 4 formations mises en avant seront visibles dans le petit catalogue public.')
                        ->default(false),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('featured_sort_order')
                        ->label('Ordre mise en avant')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }
}
