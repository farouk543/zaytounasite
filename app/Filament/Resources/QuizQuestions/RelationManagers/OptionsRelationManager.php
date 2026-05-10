<?php

namespace App\Filament\Resources\QuizQuestions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $title = 'Réponses / options';

    public function form(Schema $schema): Schema
    {
        $questionType = $this->getOwnerRecord()?->question_type ?? 'single_choice';
        $isMatching   = $questionType === 'matching';
        $isOrdering   = $questionType === 'ordering';

        return $schema->schema([
            Section::make($this->sectionLabel($questionType))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('option_text')
                        ->label($isMatching ? 'Élément gauche (FR)' : 'Réponse / élément (FR)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('option_text_ar')
                        ->label($isMatching ? 'Élément gauche (AR)' : 'Réponse (AR)')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('option_text_en')
                        ->label($isMatching ? 'Élément gauche (EN)' : 'Réponse (EN)')
                        ->maxLength(255),

                    // Matching only — right side of the pair
                    Forms\Components\TextInput::make('match_text')
                        ->label('Correspondance / côté droit (FR)')
                        ->maxLength(255)
                        ->visible($isMatching)
                        ->required($isMatching)
                        ->helperText('Ce texte apparaîtra côté droit. L\'étudiant doit le relier au texte gauche.'),

                    Forms\Components\TextInput::make('match_text_ar')
                        ->label('Correspondance (AR)')
                        ->maxLength(255)
                        ->visible($isMatching),

                    Forms\Components\TextInput::make('match_text_en')
                        ->label('Correspondance (EN)')
                        ->maxLength(255)
                        ->visible($isMatching),

                    Forms\Components\TextInput::make('sort_order')
                        ->label($isOrdering ? 'Position correcte (1, 2, 3…)' : 'Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText($isOrdering ? 'L\'étudiant doit retrouver cet ordre.' : null),

                    // is_correct not relevant for matching (correctness = right pair) or ordering
                    Forms\Components\Toggle::make('is_correct')
                        ->label('Bonne réponse')
                        ->default(false)
                        ->visible(! in_array($questionType, ['matching', 'ordering'])),
                ]),
        ]);
    }

    private function sectionLabel(string $type): string
    {
        return match ($type) {
            'matching'   => 'Paire (gauche ↔ droite)',
            'ordering'   => 'Élément à ordonner',
            'fill_blank' => 'Réponse attendue',
            default      => 'Option / Réponse',
        };
    }

    public function table(Table $table): Table
    {
        $questionType = $this->getOwnerRecord()?->question_type ?? 'single_choice';
        $isMatching   = $questionType === 'matching';

        return $table
            ->recordTitleAttribute('option_text')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('option_text')
                    ->label($isMatching ? 'Gauche' : 'Réponse')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('match_text')
                    ->label('Droite (correspondance)')
                    ->visible($isMatching)
                    ->limit(50)
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correcte')
                    ->boolean()
                    ->visible(! in_array($questionType, ['matching', 'ordering'])),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter ' . ($isMatching ? 'une paire' : 'une réponse')),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
