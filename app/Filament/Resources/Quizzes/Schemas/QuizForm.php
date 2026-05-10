<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Course;
use App\Models\CoursePackItem;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Affectation')
                ->description('Le quiz peut être lié à une course simple ou à un item de pack de type quiz.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('course_id')
                        ->label('Course simple')
                        ->options(fn () => Course::query()
                            ->orderBy('title')
                            ->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (filled($state)) {
                                $set('course_pack_item_id', null);
                            }
                        }),

                    Forms\Components\Select::make('course_pack_item_id')
                        ->label('Item de pack')
                        ->options(fn () => CoursePackItem::query()
                            ->where('item_type', 'quiz')
                            ->orderBy('title')
                            ->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (filled($state)) {
                                $set('course_id', null);
                            }
                        })
                        ->helperText('Choisis un item de pack de type quiz.'),

                    Forms\Components\Placeholder::make('assignment_rule')
                        ->label('Règle')
                        ->content('Un quiz doit être lié soit à une course, soit à un item de pack, mais pas les deux.'),
                ]),

            Forms\Components\Placeholder::make('create_notice')
                ->label('')
                ->content('💡 Enregistrez d\'abord le quiz. Vous pourrez ensuite ajouter les questions depuis la page de modification.')
                ->visible(fn (string $operation) => $operation === 'create'),

            Section::make('Contenu du quiz')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre (FR)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('title_ar')
                        ->label('Titre (AR)')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('title_en')
                        ->label('Titre (EN)')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description (FR)')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description_ar')
                        ->label('Description (AR)')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description_en')
                        ->label('Description (EN)')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Paramètres du quiz')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('passing_score')
                        ->label('Score de réussite (%)')
                        ->numeric()
                        ->default(50)
                        ->required()
                        ->minValue(0)
                        ->maxValue(100),

                    Forms\Components\TextInput::make('time_limit_minutes')
                        ->label('Temps limite (min)')
                        ->numeric()
                        ->nullable()
                        ->minValue(1),

                    Forms\Components\TextInput::make('max_attempts')
                        ->label('Tentatives max')
                        ->numeric()
                        ->nullable()
                        ->minValue(1),

                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié')
                        ->default(false),

                    Forms\Components\Toggle::make('is_randomized')
                        ->label('Questions aléatoires')
                        ->default(false),

                    Forms\Components\Toggle::make('show_result_immediately')
                        ->label('Afficher résultat immédiat')
                        ->default(true),
                ]),
        ]);
    }
}