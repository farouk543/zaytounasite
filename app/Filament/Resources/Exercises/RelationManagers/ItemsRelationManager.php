<?php

namespace App\Filament\Resources\Exercises\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items / Questions';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->exercise_type !== 'pdf';
    }

    // ── Types et labels ──────────────────────────────────────────────────

    public const TYPES = [
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
    ];

    private function helperForType(?string $type): ?string
    {
        return match ($type) {
            'matching'       => 'Dans "Réponses" : answer_text = gauche, match_text = droite.',
            'ordering'       => 'Dans "Réponses" : sort_order = position correcte (1, 2, 3…).',
            'fill_blank'     => 'Écris ___ dans l\'énoncé pour chaque blanc. Dans "Réponses" : une entrée par blanc, blank_index = 0 pour le 1er ___, 1 pour le 2e, etc.',
            'word_bank'      => 'Écris ___ dans l\'énoncé. Dans "Réponses" : tous les mots disponibles. Pour les mots corrects : is_correct = true + blank_index = le blanc qu\'ils remplissent. Les distracteurs : is_correct = false, blank_index vide.',
            'categorization' => 'Dans "Réponses" : answer_text = élément, category = la catégorie cible.',
            'multiple_choice'=> 'Plusieurs options peuvent être correctes (is_correct = true pour chacune).',
            'short_answer'   => 'Dans "Réponses" : liste les formulations acceptées. Choisir le mode de comparaison (exact / contient / commence par).',
            'calculation'    => 'Dans "Réponses" : correct_value = valeur numérique, tolerance = marge acceptée (0 = exact).',
            default          => null,
        };
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Item')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('item_type')
                        ->label('Type')
                        ->options(self::TYPES)
                        ->default('single_choice')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText(fn ($get) => $this->helperForType($get('item_type'))),

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

    // ── Table ─────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('statement')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('item_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'single_choice'   => 'Choix unique',
                        'multiple_choice' => 'Choix multiple',
                        'true_false'      => 'Vrai/Faux',
                        'matching'        => 'Relier',
                        'ordering'        => 'Ordonner',
                        'fill_blank'      => 'Trous',
                        'word_bank'       => 'Banque mots',
                        'categorization'  => 'Catégoriser',
                        'short_answer'    => 'Réponse courte',
                        'calculation'     => 'Calcul',
                        default           => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'matching'       => 'info',
                        'ordering'       => 'warning',
                        'categorization' => 'success',
                        'calculation'    => 'danger',
                        'word_bank'      => 'warning',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('statement')
                    ->label('Énoncé')
                    ->limit(60)
                    ->searchable(),

                Tables\Columns\TextColumn::make('points')
                    ->label('Pts')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('answers_count')
                    ->label('Réponses')
                    ->counts('answers')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()->label('Ajouter un item'),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier'),

                Action::make('answers')
                    ->label('Réponses')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->url(fn ($record) => \App\Filament\Resources\ExerciseItems\ExerciseItemResource::getUrl('edit', ['record' => $record])),

                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
