<?php

namespace App\Filament\Resources\Quizzes\RelationManagers;

use App\Filament\Resources\QuizQuestions\RelationManagers\OptionsRelationManager;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Questions';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Question')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('question_type')
                        ->label('Type de question')
                        ->options([
                            'single_choice'   => '🔘 Choix unique',
                            'multiple_choice' => '☑️ Choix multiple',
                            'true_false'      => '✅ Vrai / Faux',
                            'short_answer'    => '✏️ Réponse courte (texte libre)',
                            'fill_blank'      => '📝 Compléter les blancs (___)',
                            'matching'        => '🔗 Relier par une flèche',
                            'ordering'        => '↕️ Remettre dans l\'ordre',
                        ])
                        ->default('single_choice')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText(fn ($get) => match ($get('question_type')) {
                            'matching'        => 'Dans "Réponses" : option_text = côté gauche, match_text = côté droit.',
                            'ordering'        => 'Dans "Réponses" : sort_order = position correcte (1, 2, 3…).',
                            'fill_blank'      => 'Écris ___ dans la question pour marquer les blancs. Bonne réponse = is_correct true.',
                            'multiple_choice' => 'Plusieurs options peuvent être correctes (is_correct = true).',
                            default           => null,
                        }),

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

                    Forms\Components\Textarea::make('question_text')
                        ->label('Question (FR)')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('question_text_ar')
                        ->label('Question (AR)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('question_text_en')
                        ->label('Question (EN)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('image_path')
                        ->label('Image illustrative (optionnel)')
                        ->image()
                        ->disk('public')
                        ->directory('quiz/questions')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(4096)
                        ->columnSpanFull()
                        ->helperText('Affichee au-dessus du texte de la question. Idéal pour les schemas, graphiques ou photos.'),

                    Forms\Components\Textarea::make('explanation')
                        ->label('Explication (FR)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('explanation_ar')
                        ->label('Explication (AR)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('explanation_en')
                        ->label('Explication (EN)')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('question_text')
        ->defaultSort('sort_order')
        ->columns([
            Tables\Columns\TextColumn::make('sort_order')
                ->label('#')
                ->sortable(),

            Tables\Columns\TextColumn::make('question_type')
                ->label('Type')
                ->badge()
                ->formatStateUsing(fn (?string $state) => match ($state) {
                    'single_choice'   => 'Choix unique',
                    'multiple_choice' => 'Choix multiple',
                    'true_false'      => 'Vrai / Faux',
                    'short_answer'    => 'Réponse courte',
                    'fill_blank'      => 'Compléter',
                    'matching'        => 'Relier',
                    'ordering'        => 'Ordonner',
                    default => '-',
                })
                ->color(fn (?string $state) => match ($state) {
                    'matching'  => 'info',
                    'ordering'  => 'warning',
                    'fill_blank' => 'success',
                    default     => 'gray',
                }),

            Tables\Columns\TextColumn::make('question_text')
                ->label('Question')
                ->limit(60)
                ->searchable(),

            Tables\Columns\TextColumn::make('points')
                ->label('Points')
                ->sortable(),

            Tables\Columns\TextColumn::make('options_count')
                ->label('Options')
                ->counts('options')
                ->sortable(),
        ])
        ->headerActions([
            CreateAction::make()
                ->label('Ajouter question'),
        ])
        ->recordActions([
            EditAction::make()->label('Modifier'),

            Action::make('options')
                ->label('Réponses')
                ->icon('heroicon-o-list-bullet')
                ->url(fn ($record) => \App\Filament\Resources\QuizQuestions\QuizQuestionResource::getUrl('edit', ['record' => $record])),

            DeleteAction::make()->label('Supprimer'),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            OptionsRelationManager::class,
        ];
    }
}