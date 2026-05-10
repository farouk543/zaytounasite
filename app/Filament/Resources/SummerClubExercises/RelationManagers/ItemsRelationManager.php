<?php

namespace App\Filament\Resources\SummerClubExercises\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items interactifs';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Consigne')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options(self::typeOptions())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            foreach (self::virtualFields() as $field) {
                                $set($field, null);
                            }
                        })
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

                    Forms\Components\Textarea::make('instruction')
                        ->label('Instruction')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('question')
                        ->label('Question')
                        ->rows(3)
                        ->helperText(fn ($get) => $get('type') === 'fill_blank'
                            ? 'Utiliser ____ pour indiquer le vide. Exemple : Le soleil se lève à l’____.'
                            : null)
                        ->columnSpanFull(),
                ]),

            Actions::make([
                Action::make('use_example')
                    ->label('Utiliser un exemple')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->visible(fn ($get) => filled($get('type')))
                    ->action(fn ($get, $set) => self::fillExampleForType($get('type'), $set)),
            ]),

            Section::make('Média')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('media_type')
                        ->label('Type de média')
                        ->options([
                            'image' => 'Image',
                            'video' => 'Vidéo',
                            'audio' => 'Audio',
                        ])
                        ->nullable()
                        ->live(),

                    Forms\Components\TextInput::make('media_url')
                        ->label('Lien média externe')
                        ->placeholder('https://...')
                        ->url()
                        ->nullable(),

                    Forms\Components\FileUpload::make('media_path')
                        ->label('Fichier média')
                        ->disk('public')
                        ->directory('summer-club/exercise-media')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'image/*',
                            'video/*',
                            'audio/*',
                        ])
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),
                ]),

            Section::make('Choix de réponses')
                ->visible(fn ($get) => $get('type') === 'multiple_choice')
                ->schema([
                    Forms\Components\Repeater::make('mcq_options')
                        ->label('')
                        ->minItems(2)
                        ->defaultItems(4)
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('key')
                                ->label('Clé')
                                ->default(fn ($state, $component) => null)
                                ->maxLength(8)
                                ->helperText('Ex. a, b, c'),

                            Forms\Components\TextInput::make('text')
                                ->label('Réponse')
                                ->required()
                                ->columnSpan(1),

                            Forms\Components\Toggle::make('is_correct')
                                ->label('Bonne réponse')
                                ->default(false),
                        ])
                        ->columnSpanFull(),
                ]),

            Section::make('Bonne réponse')
                ->visible(fn ($get) => $get('type') === 'true_false')
                ->schema([
                    Forms\Components\Select::make('tf_correct_answer')
                        ->label('Réponse correcte')
                        ->options([
                            'true' => 'Vrai',
                            'false' => 'Faux',
                        ])
                        ->required()
                        ->native(false),
                ]),

            Section::make('Réponses acceptées')
                ->visible(fn ($get) => in_array($get('type'), ['fill_blank', 'short_answer'], true))
                ->schema([
                    Forms\Components\Repeater::make('blank_answers')
                        ->label('Réponses acceptées')
                        ->visible(fn ($get) => $get('type') === 'fill_blank')
                        ->minItems(1)
                        ->schema([
                            Forms\Components\TextInput::make('answer')
                                ->label('Réponse')
                                ->required(),
                        ]),

                    Forms\Components\Repeater::make('short_answers')
                        ->label('Réponses acceptées')
                        ->visible(fn ($get) => $get('type') === 'short_answer')
                        ->minItems(1)
                        ->schema([
                            Forms\Components\TextInput::make('answer')
                                ->label('Réponse')
                                ->required(),
                        ]),
                ]),

            Section::make('Éléments à relier')
                ->visible(fn ($get) => $get('type') === 'matching')
                ->schema([
                    Forms\Components\Repeater::make('matching_pairs')
                        ->label('')
                        ->minItems(2)
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('left_text')
                                ->label('Élément gauche')
                                ->required(),

                            Forms\Components\TextInput::make('right_text')
                                ->label('Correspondance droite')
                                ->required(),
                        ]),
                ]),

            Section::make('Ordre correct')
                ->visible(fn ($get) => $get('type') === 'ordering')
                ->schema([
                    Forms\Components\Repeater::make('ordering_items')
                        ->label('Éléments')
                        ->minItems(2)
                        ->reorderable()
                        ->schema([
                            Forms\Components\TextInput::make('text')
                                ->label('Texte')
                                ->required(),
                        ]),
                ]),

            Section::make('Éléments à placer')
                ->visible(fn ($get) => $get('type') === 'drag_drop')
                ->schema([
                    Forms\Components\Repeater::make('drag_items')
                        ->label('')
                        ->minItems(1)
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('item_text')
                                ->label('Élément')
                                ->required(),

                            Forms\Components\TextInput::make('zone_text')
                                ->label('Zone')
                                ->required(),
                        ]),
                ]),

            Section::make('Légendes attendues')
                ->visible(fn ($get) => $get('type') === 'image_labeling')
                ->schema([
                    Forms\Components\Placeholder::make('image_notice')
                        ->label('Image')
                        ->content('Ajoutez une image dans la section Média avec le type Image.'),

                    Forms\Components\Repeater::make('image_labels')
                        ->label('')
                        ->minItems(1)
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('label_text')
                                ->label('Label affiché')
                                ->required(),

                            Forms\Components\TextInput::make('expected_answer')
                                ->label('Réponse attendue')
                                ->required(),
                        ]),
                ]),

            Section::make('Correction')
                ->schema([
                    Forms\Components\Textarea::make('explanation')
                        ->label('Explication')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('instruction')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::typeOptions()[$state] ?? '-'),

                Tables\Columns\TextColumn::make('instruction')
                    ->label('Instruction')
                    ->limit(70)
                    ->searchable(),

                Tables\Columns\TextColumn::make('media_type')
                    ->label('Média')
                    ->badge()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un item')
                    ->mutateDataUsing(fn (array $data): array => self::prepareDataForSave($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->fillForm(fn (Model $record): array => self::formDataFromRecord($record))
                    ->mutateDataUsing(fn (array $data): array => self::prepareDataForSave($data)),

                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function typeOptions(): array
    {
        return [
            'multiple_choice' => 'Choix multiple',
            'true_false' => 'Vrai / Faux',
            'fill_blank' => 'Compléter le vide',
            'short_answer' => 'Réponse courte',
            'matching' => 'Relier par flèche',
            'ordering' => 'Ordonner',
            'drag_drop' => 'Glisser-déposer',
            'image_labeling' => 'Image à légender',
        ];
    }

    private static function virtualFields(): array
    {
        return [
            'mcq_options',
            'tf_correct_answer',
            'blank_answers',
            'short_answers',
            'matching_pairs',
            'ordering_items',
            'drag_items',
            'image_labels',
        ];
    }

    private static function fillExampleForType(?string $type, callable $set): void
    {
        foreach (self::virtualFields() as $field) {
            $set($field, null);
        }

        match ($type) {
            'multiple_choice' => self::fillMultipleChoiceExample($set),
            'true_false' => $set('tf_correct_answer', 'true'),
            'fill_blank' => self::fillBlankExample($set),
            'short_answer' => self::fillShortAnswerExample($set),
            'matching' => self::fillMatchingExample($set),
            'ordering' => self::fillOrderingExample($set),
            'drag_drop' => self::fillDragDropExample($set),
            'image_labeling' => self::fillImageLabelingExample($set),
            default => null,
        };
    }

    private static function fillMultipleChoiceExample(callable $set): void
    {
        $set('instruction', 'Choisis la bonne réponse.');
        $set('question', 'Quelle est la capitale de la France ?');
        $set('mcq_options', [
            ['key' => 'a', 'text' => 'Paris', 'is_correct' => true],
            ['key' => 'b', 'text' => 'Londres', 'is_correct' => false],
            ['key' => 'c', 'text' => 'Madrid', 'is_correct' => false],
            ['key' => 'd', 'text' => 'Rome', 'is_correct' => false],
        ]);
    }

    private static function fillBlankExample(callable $set): void
    {
        $set('instruction', 'Complète la phrase.');
        $set('question', 'Le soleil se lève à l’____.');
        $set('blank_answers', [
            ['answer' => 'est'],
            ['answer' => 'Est'],
        ]);
    }

    private static function fillShortAnswerExample(callable $set): void
    {
        $set('instruction', 'Réponds en un mot.');
        $set('question', 'Quelle est la capitale de la France ?');
        $set('short_answers', [
            ['answer' => 'Paris'],
            ['answer' => 'paris'],
        ]);
    }

    private static function fillMatchingExample(callable $set): void
    {
        $set('instruction', 'Relie chaque mot à sa traduction.');
        $set('matching_pairs', [
            ['left_text' => 'Chat', 'right_text' => 'Cat'],
            ['left_text' => 'Chien', 'right_text' => 'Dog'],
            ['left_text' => 'Oiseau', 'right_text' => 'Bird'],
        ]);
    }

    private static function fillOrderingExample(callable $set): void
    {
        $set('instruction', 'Remets les mots dans le bon ordre.');
        $set('ordering_items', [
            ['text' => 'Je'],
            ['text' => 'mange'],
            ['text' => 'une pomme'],
        ]);
    }

    private static function fillDragDropExample(callable $set): void
    {
        $set('instruction', 'Associe chaque élément à sa catégorie.');
        $set('drag_items', [
            ['item_text' => 'Pomme', 'zone_text' => 'Fruit'],
            ['item_text' => 'Banane', 'zone_text' => 'Fruit'],
            ['item_text' => 'Carotte', 'zone_text' => 'Légume'],
        ]);
    }

    private static function fillImageLabelingExample(callable $set): void
    {
        $set('instruction', 'Observe l’image puis complète les légendes.');
        $set('media_type', 'image');
        $set('image_labels', [
            ['label_text' => 'Partie 1', 'expected_answer' => 'Racine'],
            ['label_text' => 'Partie 2', 'expected_answer' => 'Feuille'],
        ]);
    }

    private static function formDataFromRecord(Model $record): array
    {
        $data = $record->attributesToArray();
        $options = $record->options ?? [];
        $correct = $record->correct_answer ?? [];

        return array_merge($data, match ($record->type) {
            'multiple_choice' => [
                'mcq_options' => collect($options)->map(fn ($option) => [
                    'key' => $option['key'] ?? null,
                    'text' => $option['text'] ?? null,
                    'is_correct' => in_array($option['key'] ?? null, $correct['answers'] ?? [], true),
                ])->values()->all(),
            ],
            'true_false' => [
                'tf_correct_answer' => $correct['answer'] ?? null,
            ],
            'fill_blank' => [
                'blank_answers' => collect($correct['answers'] ?? [])->map(fn ($answer) => ['answer' => $answer])->values()->all(),
            ],
            'short_answer' => [
                'short_answers' => collect($correct['answers'] ?? [])->map(fn ($answer) => ['answer' => $answer])->values()->all(),
            ],
            'matching' => [
                'matching_pairs' => collect($correct['pairs'] ?? [])->map(function ($pair) use ($options) {
                    $left = collect($options['left'] ?? [])->firstWhere('key', $pair['left'] ?? null);
                    $right = collect($options['right'] ?? [])->firstWhere('key', $pair['right'] ?? null);

                    return [
                        'left_text' => $left['text'] ?? null,
                        'right_text' => $right['text'] ?? null,
                    ];
                })->values()->all(),
            ],
            'ordering' => [
                'ordering_items' => collect($options)->map(fn ($option) => ['text' => $option['text'] ?? null])->values()->all(),
            ],
            'drag_drop' => [
                'drag_items' => collect($correct['matches'] ?? [])->map(function ($match) use ($options) {
                    $item = collect($options['items'] ?? [])->firstWhere('key', $match['item'] ?? null);
                    $zone = collect($options['zones'] ?? [])->firstWhere('key', $match['zone'] ?? null);

                    return [
                        'item_text' => $item['text'] ?? null,
                        'zone_text' => $zone['text'] ?? null,
                    ];
                })->values()->all(),
            ],
            'image_labeling' => [
                'image_labels' => collect($correct['answers'] ?? [])->map(function ($answer) use ($options) {
                    $label = collect($options['labels'] ?? [])->firstWhere('key', $answer['label'] ?? null);

                    return [
                        'label_text' => $label['text'] ?? null,
                        'expected_answer' => $answer['answer'] ?? null,
                    ];
                })->values()->all(),
            ],
            default => [],
        });
    }

    private static function prepareDataForSave(array $data): array
    {
        [$options, $correctAnswer] = self::buildJsonPayload($data);

        $data['options'] = $options;
        $data['correct_answer'] = $correctAnswer;

        foreach (self::virtualFields() as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private static function buildJsonPayload(array $data): array
    {
        return match ($data['type'] ?? null) {
            'multiple_choice' => self::buildMultipleChoice($data),
            'true_false' => self::buildTrueFalse($data),
            'fill_blank' => self::buildAnswersPayload($data, 'blank_answers', shouldRequireBlank: true),
            'short_answer' => self::buildAnswersPayload($data, 'short_answers'),
            'matching' => self::buildMatching($data),
            'ordering' => self::buildOrdering($data),
            'drag_drop' => self::buildDragDrop($data),
            'image_labeling' => self::buildImageLabeling($data),
            default => [null, null],
        };
    }

    private static function buildMultipleChoice(array $data): array
    {
        $rows = collect($data['mcq_options'] ?? [])
            ->filter(fn ($row) => filled($row['text'] ?? null))
            ->values();

        if ($rows->count() < 2) {
            throw ValidationException::withMessages(['mcq_options' => 'Ajoutez au moins 2 réponses.']);
        }

        $normalizedRows = $rows->map(fn ($row, int $index) => [
            'key' => filled($row['key'] ?? null) ? (string) $row['key'] : chr(97 + $index),
            'text' => (string) $row['text'],
            'is_correct' => (bool) ($row['is_correct'] ?? false),
        ])->values();

        $options = $normalizedRows
            ->map(fn ($row) => [
                'key' => $row['key'],
                'text' => $row['text'],
            ])
            ->values()
            ->all();

        $answers = $normalizedRows
            ->filter(fn ($row) => $row['is_correct'])
            ->pluck('key')
            ->values()
            ->all();

        if ($answers === []) {
            throw ValidationException::withMessages(['mcq_options' => 'Cochez au moins une bonne réponse.']);
        }

        return [$options, ['answers' => $answers]];
    }

    private static function buildTrueFalse(array $data): array
    {
        if (! in_array($data['tf_correct_answer'] ?? null, ['true', 'false'], true)) {
            throw ValidationException::withMessages(['tf_correct_answer' => 'Choisissez Vrai ou Faux.']);
        }

        return [
            [
                ['key' => 'true', 'text' => 'Vrai'],
                ['key' => 'false', 'text' => 'Faux'],
            ],
            ['answer' => $data['tf_correct_answer']],
        ];
    }

    private static function buildAnswersPayload(array $data, string $field, bool $shouldRequireBlank = false): array
    {
        if ($shouldRequireBlank && ! str_contains((string) ($data['question'] ?? ''), '____')) {
            throw ValidationException::withMessages(['question' => 'La question doit contenir ____.']);
        }

        $answers = collect($data[$field] ?? [])
            ->pluck('answer')
            ->filter(fn ($answer) => filled($answer))
            ->values()
            ->all();

        if ($answers === []) {
            throw ValidationException::withMessages([$field => 'Ajoutez au moins une réponse acceptée.']);
        }

        return [null, ['answers' => $answers]];
    }

    private static function buildMatching(array $data): array
    {
        $rows = collect($data['matching_pairs'] ?? [])
            ->filter(fn ($row) => filled($row['left_text'] ?? null) && filled($row['right_text'] ?? null))
            ->values();

        if ($rows->count() < 2) {
            throw ValidationException::withMessages(['matching_pairs' => 'Ajoutez au moins 2 paires.']);
        }

        return [
            [
                'left' => $rows->map(fn ($row, int $index) => ['key' => 'l' . ($index + 1), 'text' => $row['left_text']])->all(),
                'right' => $rows->map(fn ($row, int $index) => ['key' => 'r' . ($index + 1), 'text' => $row['right_text']])->all(),
            ],
            [
                'pairs' => $rows->map(fn ($row, int $index) => ['left' => 'l' . ($index + 1), 'right' => 'r' . ($index + 1)])->all(),
            ],
        ];
    }

    private static function buildOrdering(array $data): array
    {
        $rows = collect($data['ordering_items'] ?? [])
            ->filter(fn ($row) => filled($row['text'] ?? null))
            ->values();

        if ($rows->count() < 2) {
            throw ValidationException::withMessages(['ordering_items' => 'Ajoutez au moins 2 éléments.']);
        }

        $options = $rows->map(fn ($row, int $index) => ['key' => (string) ($index + 1), 'text' => $row['text']])->all();

        return [$options, ['order' => collect($options)->pluck('key')->all()]];
    }

    private static function buildDragDrop(array $data): array
    {
        $rows = collect($data['drag_items'] ?? [])
            ->filter(fn ($row) => filled($row['item_text'] ?? null) && filled($row['zone_text'] ?? null))
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['drag_items' => 'Ajoutez au moins un élément.']);
        }

        $zones = $rows->pluck('zone_text')->unique()->values();

        return [
            [
                'items' => $rows->map(fn ($row, int $index) => ['key' => 'i' . ($index + 1), 'text' => $row['item_text']])->all(),
                'zones' => $zones->map(fn ($zone, int $index) => ['key' => 'z' . ($index + 1), 'text' => $zone])->all(),
            ],
            [
                'matches' => $rows->map(function ($row, int $index) use ($zones) {
                    return [
                        'item' => 'i' . ($index + 1),
                        'zone' => 'z' . ($zones->search($row['zone_text']) + 1),
                    ];
                })->all(),
            ],
        ];
    }

    private static function buildImageLabeling(array $data): array
    {
        if (($data['media_type'] ?? null) !== 'image' || (blank($data['media_path'] ?? null) && blank($data['media_url'] ?? null))) {
            throw ValidationException::withMessages(['media_path' => 'Ajoutez une image pour cet exercice.']);
        }

        $rows = collect($data['image_labels'] ?? [])
            ->filter(fn ($row) => filled($row['label_text'] ?? null) && filled($row['expected_answer'] ?? null))
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['image_labels' => 'Ajoutez au moins une légende.']);
        }

        return [
            [
                'labels' => $rows->map(fn ($row, int $index) => ['key' => 'l' . ($index + 1), 'text' => $row['label_text']])->all(),
            ],
            [
                'answers' => $rows->map(fn ($row, int $index) => ['label' => 'l' . ($index + 1), 'answer' => $row['expected_answer']])->all(),
            ],
        ];
    }
}
