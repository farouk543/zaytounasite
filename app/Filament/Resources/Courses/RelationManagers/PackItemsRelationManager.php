<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PackItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'packItems';

    protected static ?string $title = 'Contenu du pack';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Type & structure')
                ->description("Definis le type d'element inclus dans ce pack.")
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('item_type')
                        ->label("Type d'element")
                        ->options([
                            'course'    => 'Course liee',
                            'series'    => 'Serie',
                            'quiz'      => 'Quiz',
                            'exercise'  => 'Exercice',
                            'exam_prep' => 'Preparation examen',
                            'resource'  => 'Ressource',
                        ])
                        ->required()
                        ->native(false)
                        ->default('course')
                        ->live()
                        ->afterStateUpdated(function ($set, ?string $state) {
                            if ($state !== 'course') {
                                $set('linked_course_id', null);
                            }

                            if (in_array($state, ['quiz', 'exercise'], true)) {
                                $set('resource_path', null);
                                $set('resource_paths', null);
                                $set('external_url', null);
                            }
                        }),

                    Forms\Components\Select::make('linked_course_id')
                        ->label('Cours existant lie')
                        ->options(function () {
                            $pack = $this->getOwnerRecord();

                            return Course::query()
                                ->where('subject_id', $pack->subject_id)
                                ->whereIn('delivery_type', ['course', 'series', 'exam'])
                                ->whereKeyNot($pack->id)
                                ->orderBy('title')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->visible(fn ($get) => $get('item_type') === 'course')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (! $state) {
                                return;
                            }

                            $course = Course::find($state);

                            if (! $course) {
                                return;
                            }

                            $set('title', $course->title);
                            $set('title_ar', $course->title_ar);
                            $set('title_en', $course->title_en);
                            $set('description', $course->description);
                            $set('description_ar', $course->description_ar);
                            $set('description_en', $course->description_en);
                            $set('duration_minutes', $course->duration_minutes);
                        })
                        ->helperText('Selectionne un vrai cours deja cree dans la meme matiere.'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(function () {
                            $last = $this->getOwnerRecord()
                                ->packItems()
                                ->max('sort_order');

                            return is_null($last) ? 1 : $last + 1;
                        })
                        ->required(),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duree estimee (minutes)')
                        ->numeric()
                        ->nullable(),
                ]),

            Section::make('Titres')
                ->description('Titres visibles selon la langue du site.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre (FR)')
                        ->required()
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('title_ar')
                        ->label('Titre (AR)')
                        ->nullable(),

                    Forms\Components\TextInput::make('title_en')
                        ->label('Titre (EN)')
                        ->nullable(),
                ]),

            Section::make('Descriptions')
                ->columns(1)
                ->schema([
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

            Section::make('Ressource / acces')
                ->description("Utilise ces champs selon le type d'element.")
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('resource_path')
                        ->label('Fichier PDF principal')
                        ->disk('private')
                        ->directory('packs/resources')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(25000)
                        ->visible(fn ($get) => in_array($get('item_type'), ['course', 'series', 'resource', 'exam_prep'], true))
                        ->helperText('PDF principal de cet element.'),

                    Forms\Components\FileUpload::make('resource_paths')
                        ->label('PDFs supplementaires (plusieurs fichiers)')
                        ->disk('private')
                        ->directory('packs/resources')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(25000)
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->visible(fn ($get) => in_array($get('item_type'), ['course', 'series', 'resource', 'exam_prep'], true))
                        ->helperText('Ajoute autant de PDFs que necessaire.')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('video_url')
                        ->label('Video YouTube / Vimeo (embed)')
                        ->url()
                        ->maxLength(500)
                        ->nullable()
                        ->columnSpanFull()
                        ->placeholder('https://www.youtube.com/embed/...')
                        ->helperText("Colle l'URL embed YouTube ou Vimeo."),
                ]),

            Section::make('Options pedagogiques')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_required')
                        ->label('Obligatoire')
                        ->default(true),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('item_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'course'    => 'Course liee',
                        'series'    => 'Serie',
                        'quiz'      => 'Quiz',
                        'exercise'  => 'Exercice',
                        'exam_prep' => 'Preparation examen',
                        'resource'  => 'Ressource',
                        default     => '-',
                    })
                    ->colors([
                        'success' => fn (?string $state) => $state === 'course',
                        'warning' => fn (?string $state) => $state === 'quiz',
                        'primary' => fn (?string $state) => $state === 'exercise',
                        'info'    => fn (?string $state) => $state === 'resource',
                        'gray'    => fn (?string $state) => in_array($state, ['series', 'exam_prep']),
                    ]),

                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('linkedCourse.title')
                    ->label('Cours lie')
                    ->placeholder('-')
                    ->limit(35),

                IconColumn::make('is_required')
                    ->label('Obligatoire')
                    ->boolean(),

                TextColumn::make('duration_minutes')
                    ->label('Duree')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' min' : '-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un element'),

                Action::make('importExistingCourses')
                    ->label('Importer des cours existants')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\CheckboxList::make('course_ids')
                            ->label('Cours disponibles')
                            ->options(function () {
                                $pack = $this->getOwnerRecord();

                                $alreadyLinkedIds = $pack->packItems()
                                    ->whereNotNull('linked_course_id')
                                    ->pluck('linked_course_id')
                                    ->all();

                                return Course::query()
                                    ->where('subject_id', $pack->subject_id)
                                    ->whereIn('delivery_type', ['course', 'series', 'exam'])
                                    ->whereKeyNot($pack->id)
                                    ->when(
                                        ! empty($alreadyLinkedIds),
                                        fn ($query) => $query->whereNotIn('id', $alreadyLinkedIds)
                                    )
                                    ->orderBy('title')
                                    ->get()
                                    ->mapWithKeys(fn (Course $course) => [
                                        $course->id => $course->title . ' (' . match ($course->delivery_type) {
                                            'course' => 'Cours',
                                            'series' => 'Serie',
                                            'exam' => 'Examen',
                                            default => $course->delivery_type,
                                        } . ')',
                                    ])
                                    ->toArray();
                            })
                            ->columns(1)
                            ->searchable()
                            ->bulkToggleable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $pack = $this->getOwnerRecord();

                        $courseIds = $data['course_ids'] ?? [];

                        if (empty($courseIds)) {
                            Notification::make()
                                ->title('Aucun cours selectionne')
                                ->warning()
                                ->send();

                            return;
                        }

                        $courses = Course::query()
                            ->whereIn('id', $courseIds)
                            ->where('subject_id', $pack->subject_id)
                            ->whereIn('delivery_type', ['course', 'series', 'exam'])
                            ->orderBy('title')
                            ->get();

                        $nextSort = (int) ($pack->packItems()->max('sort_order') ?? 0) + 1;
                        $created = 0;

                        foreach ($courses as $course) {
                            $alreadyExists = $pack->packItems()
                                ->where('linked_course_id', $course->id)
                                ->exists();

                            if ($alreadyExists) {
                                continue;
                            }

                            $pack->packItems()->create([
                                'item_type' => 'course',
                                'title' => $course->title,
                                'title_ar' => $course->title_ar,
                                'title_en' => $course->title_en,
                                'description' => $course->description,
                                'description_ar' => $course->description_ar,
                                'description_en' => $course->description_en,
                                'linked_course_id' => $course->id,
                                'duration_minutes' => $course->duration_minutes,
                                'is_required' => true,
                                'sort_order' => $nextSort++,
                            ]);

                            $created++;
                        }

                        Notification::make()
                            ->title($created . ' cours importes dans le pack')
                            ->success()
                            ->send();
                    }),
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

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord?->delivery_type === 'pack';
    }
}