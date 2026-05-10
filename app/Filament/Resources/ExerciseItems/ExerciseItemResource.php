<?php

namespace App\Filament\Resources\ExerciseItems;

use App\Filament\Resources\ExerciseItems\Pages\EditExerciseItem;
use App\Filament\Resources\ExerciseItems\Pages\ListExerciseItems;
use App\Filament\Resources\ExerciseItems\RelationManagers\AnswersRelationManager;
use App\Models\ExerciseItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExerciseItemResource extends Resource
{
    protected static ?string $model = ExerciseItem::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Item / Question')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('item_type')
                        ->label('Type')
                        ->options([
                            'single_choice'   => '🔘 QCM — Choix unique',
                            'multiple_choice' => '☑️ QCM — Choix multiple',
                            'true_false'      => '✅ Vrai / Faux',
                            'matching'        => '🔗 Relier par une flèche',
                            'ordering'        => '↕️ Remettre dans l\'ordre',
                            'fill_blank'      => '📝 Texte à trous (___)',
                            'word_bank'       => '🏦 Banque de mots',
                            'categorization'  => '🗂️ Classer par catégorie',
                            'short_answer'    => '✏️ Réponse courte',
                            'calculation'     => '🔢 Calcul numérique',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('points')
                        ->label('Points')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->minValue(1),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\Textarea::make('statement')
                        ->label('Énoncé (FR)')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('statement_ar')
                        ->label('Énoncé (AR)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('statement_en')
                        ->label('Énoncé (EN)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('image_path')
                        ->label('Image illustrative (optionnel)')
                        ->image()
                        ->disk('public')
                        ->directory('exercises/items')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(4096)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('hint')
                        ->label('Indice (FR — optionnel)')
                        ->rows(2)
                        ->columnSpanFull()
                        ->helperText('L\'étudiant peut demander cet indice en cas de blocage.'),

                    Forms\Components\Textarea::make('explanation')
                        ->label('Correction / Explication (FR)')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Affiché après validation. Explique la bonne réponse.'),

                    Forms\Components\Textarea::make('explanation_ar')
                        ->label('Correction (AR)')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [
            AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExerciseItems::route('/'),
            'edit'  => EditExerciseItem::route('/{record}/edit'),
        ];
    }
}
