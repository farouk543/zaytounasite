<?php

namespace App\Filament\Resources\ExerciseItems\RelationManagers;

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

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'Réponses / Éléments';

    // ── Form ─────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        $item            = $this->getOwnerRecord();
        $type            = $item?->item_type ?? 'single_choice';
        $isMatching      = $type === 'matching';
        $isOrdering      = $type === 'ordering';
        $isCateg         = $type === 'categorization';
        $isCalc          = $type === 'calculation';
        $isFillBlank     = $type === 'fill_blank';
        $isWordBank      = $type === 'word_bank';
        $isShortAnswer   = $type === 'short_answer';
        $needsCorrect    = ! in_array($type, ['matching', 'ordering'], true);
        $needsBlankIndex = $isFillBlank || $isWordBank;

        return $schema->schema([
            Section::make($this->sectionLabel($type))
                ->columns(2)
                ->schema([

                    // ── Texte principal ─────────────────────────────────
                    Forms\Components\TextInput::make('answer_text')
                        ->label($this->answerTextLabel($type))
                        ->required()
                        ->maxLength(500),

                    Forms\Components\TextInput::make('answer_text_ar')
                        ->label('(AR)')
                        ->maxLength(500),

                    Forms\Components\TextInput::make('answer_text_en')
                        ->label('(EN)')
                        ->maxLength(500),

                    // ── Image pour réponse illustrée ────────────────────
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Image optionnelle')
                        ->image()
                        ->disk('public')
                        ->directory('exercises/answers')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(2048)
                        ->visible(! $isCalc && ! $isFillBlank && ! $isWordBank)
                        ->columnSpanFull(),

                    // ── Matching : côté droit ───────────────────────────
                    Forms\Components\TextInput::make('match_text')
                        ->label('Correspondance — côté droit (FR)')
                        ->maxLength(500)
                        ->visible($isMatching)
                        ->required($isMatching)
                        ->helperText('L\'étudiant relie l\'élément gauche à cet élément droit.'),

                    Forms\Components\TextInput::make('match_text_ar')
                        ->label('Correspondance (AR)')
                        ->maxLength(500)
                        ->visible($isMatching),

                    Forms\Components\TextInput::make('match_text_en')
                        ->label('Correspondance (EN)')
                        ->maxLength(500)
                        ->visible($isMatching),

                    // ── Catégorisation : catégorie cible ────────────────
                    Forms\Components\TextInput::make('category')
                        ->label('Catégorie cible (FR)')
                        ->maxLength(255)
                        ->visible($isCateg)
                        ->required($isCateg)
                        ->helperText('L\'élément doit être placé dans cette catégorie.'),

                    Forms\Components\TextInput::make('category_ar')
                        ->label('Catégorie (AR)')
                        ->maxLength(255)
                        ->visible($isCateg),

                    // ── Calcul : valeur et tolérance ────────────────────
                    Forms\Components\TextInput::make('correct_value')
                        ->label('Valeur correcte')
                        ->numeric()
                        ->step(0.0001)
                        ->visible($isCalc)
                        ->required($isCalc)
                        ->helperText('Valeur numérique exacte attendue.'),

                    Forms\Components\TextInput::make('tolerance')
                        ->label('Tolérance (±)')
                        ->numeric()
                        ->step(0.0001)
                        ->default(0)
                        ->visible($isCalc)
                        ->helperText('0 = valeur exacte. Ex: 0.5 → accepte ±0.5 autour de la valeur.'),

                    // ── Index du blanc (fill_blank, word_bank) ───────────
                    Forms\Components\TextInput::make('blank_index')
                        ->label('N° du blanc (0 = premier, 1 = deuxième…)')
                        ->numeric()
                        ->minValue(0)
                        ->nullable()
                        ->visible($needsBlankIndex)
                        ->required($isFillBlank)
                        ->helperText(
                            $isFillBlank
                                ? 'Compte les ___ dans l\'énoncé. Le premier ___ = 0, le deuxième = 1, etc.'
                                : 'Remplir uniquement pour les mots corrects (is_correct = true). Laisse vide pour les distracteurs.'
                        ),

                    // ── Mode de correspondance (short_answer) ───────────
                    Forms\Components\Select::make('match_mode')
                        ->label('Mode de comparaison')
                        ->options([
                            'exact'       => 'Exact (insensible à la casse)',
                            'contains'    => 'Contient cette chaîne',
                            'starts_with' => 'Commence par cette chaîne',
                        ])
                        ->default('exact')
                        ->native(false)
                        ->visible($isShortAnswer)
                        ->helperText('Règle utilisée pour valider la saisie de l\'étudiant.'),

                    // ── Ordre d'affichage / position correcte ────────────
                    Forms\Components\TextInput::make('sort_order')
                        ->label($isOrdering ? 'Position correcte (1, 2, 3…)' : 'Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText($isOrdering ? 'L\'étudiant doit retrouver cet ordre.' : null),

                    // ── Bonne réponse ────────────────────────────────────
                    Forms\Components\Toggle::make('is_correct')
                        ->label('Bonne réponse')
                        ->default($isFillBlank ? true : false)
                        ->visible($needsCorrect && ! $isCalc)
                        ->helperText(match ($type) {
                            'fill_blank'   => 'Toujours activé : chaque entrée ici est une réponse correcte pour ce blanc.',
                            'short_answer' => 'Cochez si cette chaîne est une réponse acceptée.',
                            'word_bank'    => 'Cochez si ce mot fait partie de la solution (à placer dans un blanc).',
                            default        => null,
                        }),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        $item            = $this->getOwnerRecord();
        $type            = $item?->item_type ?? 'single_choice';
        $isMatching      = $type === 'matching';
        $isCateg         = $type === 'categorization';
        $isCalc          = $type === 'calculation';
        $isOrdering      = $type === 'ordering';
        $isFillBlank     = $type === 'fill_blank';
        $isWordBank      = $type === 'word_bank';
        $isShortAnswer   = $type === 'short_answer';
        $needsBlankIndex = $isFillBlank || $isWordBank;

        return $table
            ->recordTitleAttribute('answer_text')
            ->defaultSort($isFillBlank ? 'blank_index' : 'sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->visible(! $isFillBlank),

                Tables\Columns\TextColumn::make('blank_index')
                    ->label('Blanc N°')
                    ->visible($needsBlankIndex)
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Blanc ' . ($state + 1) : '—')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('answer_text')
                    ->label($isMatching ? 'Gauche' : 'Réponse / Élément')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('match_text')
                    ->label('Droite')
                    ->visible($isMatching)
                    ->limit(50)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->visible($isCateg)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('correct_value')
                    ->label('Valeur correcte')
                    ->visible($isCalc)
                    ->formatStateUsing(fn ($state, $record) => filled($state)
                        ? $state . ($record->tolerance > 0 ? ' ±' . $record->tolerance : '')
                        : '—'),

                Tables\Columns\TextColumn::make('match_mode')
                    ->label('Mode')
                    ->visible($isShortAnswer)
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'contains'    => 'Contient',
                        'starts_with' => 'Commence par',
                        default       => 'Exact',
                    })
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correcte')
                    ->boolean()
                    ->visible(! in_array($type, ['matching', 'ordering', 'calculation'], true)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter ' . $this->addLabel($type)),
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

    // ── Helpers ──────────────────────────────────────────────────────────

    private function sectionLabel(string $type): string
    {
        return match ($type) {
            'matching'       => 'Paire (gauche ↔ droite)',
            'ordering'       => 'Élément à ordonner',
            'fill_blank'     => 'Réponse attendue pour ce blanc',
            'word_bank'      => 'Mot disponible',
            'categorization' => 'Élément à classer',
            'calculation'    => 'Valeur numérique correcte',
            default          => 'Option / Réponse',
        };
    }

    private function answerTextLabel(string $type): string
    {
        return match ($type) {
            'matching'       => 'Élément gauche (FR)',
            'ordering'       => 'Élément à remettre en ordre (FR)',
            'fill_blank'     => 'Mot / expression attendue (FR)',
            'word_bank'      => 'Mot de la banque (FR)',
            'categorization' => 'Élément à classer (FR)',
            'calculation'    => 'Libellé / étiquette (FR)',
            default          => 'Réponse / option (FR)',
        };
    }

    private function addLabel(string $type): string
    {
        return match ($type) {
            'matching'       => 'une paire',
            'ordering'       => 'un élément',
            'categorization' => 'un élément',
            'calculation'    => 'une valeur',
            default          => 'une réponse',
        };
    }
}
