<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Branch;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Track;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Organisation académique')
                ->description('Sélectionne la filière, le niveau, la branche si nécessaire, puis la matière.')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('track_id_virtual')
                        ->label('Filière')
                        ->placeholder('Choisir une filière')
                        ->options(fn () => Track::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('level_id_virtual', null);
                            $set('branch_id_virtual', null);
                            $set('subject_id', null);
                        })
                        ->dehydrated(false),

                    Forms\Components\Select::make('level_id_virtual')
                        ->label('Niveau')
                        ->placeholder('Choisir un niveau')
                        ->options(fn ($get) => Level::query()
                            ->where('track_id', $get('track_id_virtual'))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->disabled(fn ($get) => blank($get('track_id_virtual')))
                        ->afterStateUpdated(function ($set) {
                            $set('branch_id_virtual', null);
                            $set('subject_id', null);
                        })
                        ->dehydrated(false),

                    Forms\Components\Select::make('branch_id_virtual')
                        ->label('Branche')
                        ->placeholder('Choisir une branche')
                        ->options(fn ($get) => Branch::query()
                            ->where('level_id', $get('level_id_virtual'))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->disabled(fn ($get) => blank($get('level_id_virtual')))
                        ->visible(fn ($get) => filled($get('level_id_virtual')) && Branch::query()
                            ->where('level_id', $get('level_id_virtual'))
                            ->where('is_active', true)
                            ->exists())
                        ->afterStateUpdated(fn ($set) => $set('subject_id', null))
                        ->dehydrated(false),

                    Forms\Components\Select::make('subject_id')
                        ->label('Matière')
                        ->placeholder('Choisir une matière')
                        ->options(function ($get) {
                            $levelId = $get('level_id_virtual');
                            $branchId = $get('branch_id_virtual');

                            if (blank($levelId)) {
                                return [];
                            }

                            $hasBranches = Branch::query()
                                ->where('level_id', $levelId)
                                ->where('is_active', true)
                                ->exists();

                            return Subject::query()
                                ->where('level_id', $levelId)
                                ->where('is_active', true)
                                ->when(
                                    $hasBranches,
                                    fn ($q) => $q->where('branch_id', $branchId),
                                    fn ($q) => $q->whereNull('branch_id')
                                )
                                ->orderBy('sort_order')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->disabled(function ($get) {
                            $levelId = $get('level_id_virtual');

                            if (blank($levelId)) {
                                return true;
                            }

                            $hasBranches = Branch::query()
                                ->where('level_id', $levelId)
                                ->where('is_active', true)
                                ->exists();

                            return $hasBranches && blank($get('branch_id_virtual'));
                        }),
                ]),

            Section::make('Informations du produit')
                ->description('Titres, slug et image de couverture.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre (FR)')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($get, $set, ?string $state) {
                            if (blank($get('slug')) && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->helperText('URL-friendly. Unique par matière.')
                        ->maxLength(120)
                        ->rules(function ($get, $record) {
                            $rule = Rule::unique('courses', 'slug')
                                ->where('subject_id', $get('subject_id'));

                            if ($record?->id) {
                                $rule = $rule->ignore($record->id);
                            }

                            return [$rule];
                        })
                        ->validationMessages([
                            'unique' => 'Ce slug existe déjà pour cette matière. Choisissez un slug différent.',
                        ]),

                    Forms\Components\TextInput::make('title_ar')
                        ->label('Titre (AR)')
                        ->nullable(),

                    Forms\Components\TextInput::make('title_en')
                        ->label('Titre (EN)')
                        ->nullable(),

                    Forms\Components\FileUpload::make('thumbnail_path')
                        ->label('Image de couverture')
                        ->disk('public')
                        ->image()
                        ->directory('courses/thumbnails')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(4096),
                ]),

            Section::make('Descriptions')
                ->description('Le front affichera la description selon la langue sélectionnée.')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Description (FR)')
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description_ar')
                        ->label('Description (AR)')
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description_en')
                        ->label('Description (EN)')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),

            Section::make('Type de produit & livraison')
                ->description('Cours, série, examen individuel ou pack global.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('delivery_type')
                        ->label('Type de produit')
                        ->options([
                            'course' => 'Cours individuel',
                            'series' => 'Série individuelle',
                            'exam'   => 'Examen individuel',
                            'pack'   => 'Pack course',
                        ])
                        ->default('course')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Pack course = produit global contenant plusieurs PDFs et un contenu organisé en pack.')
                        ->afterStateUpdated(function ($set, ?string $state) {
                            if (in_array($state, ['course', 'series', 'exam'], true)) {
                                $set('pack_pdf_paths', null);
                                $set('access_note', null);
                                $set('has_preview', false);
                            } else {
                                $set('content_path', null);
                            }
                        }),

                    Forms\Components\FileUpload::make('content_path')
                        ->label('Fichier PDF principal')
                        ->disk('private')
                        ->directory('courses/content')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(25000)
                        ->helperText('PDF principal du cours, de la série ou de l\'examen.')
                        ->visible(fn ($get) => in_array(($get('delivery_type') ?? 'course'), ['course', 'series', 'exam'], true))
                        ->required(fn ($get) => in_array(($get('delivery_type') ?? 'course'), ['course', 'series', 'exam'], true)),

                    Forms\Components\Placeholder::make('pack_info_notice')
                        ->label('')
                        ->content('📦 Le contenu du pack se gère après enregistrement dans la section "Contenu du pack". Vous pouvez aussi ajouter ici plusieurs PDFs globaux pour le pack.')
                        ->columnSpanFull()
                        ->visible(fn ($get) => ($get('delivery_type') ?? 'course') === 'pack'),

                    Forms\Components\FileUpload::make('pack_pdf_paths')
                        ->label('PDFs du pack')
                        ->disk('private')
                        ->directory('packs/content')
                        ->acceptedFileTypes(['application/pdf'])
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->maxSize(25000)
                        ->helperText('Ajoute plusieurs fichiers PDF pour ce pack.')
                        ->visible(fn ($get) => ($get('delivery_type') ?? 'course') === 'pack')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('access_note')
                        ->label('Description du pack (FR)')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Exemple : Ce pack contient tous les cours, séries et examens de la matière.')
                        ->visible(fn ($get) => ($get('delivery_type') ?? 'course') === 'pack'),
                ]),

            Section::make('Pedagogie & mise en avant')
                ->description('Niveau, duree estimee et mise en avant sur la page catalogue.')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('difficulty_level')
                        ->label('Niveau de difficulte')
                        ->options([
                            'beginner'     => 'Debutant',
                            'intermediate' => 'Intermediaire',
                            'advanced'     => 'Avance',
                        ])
                        ->native(false)
                        ->nullable()
                        ->placeholder('Non specifie'),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duree (minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(9999)
                        ->nullable()
                        ->placeholder('Ex: 90')
                        ->helperText('60 = 1h, 90 = 1h30, etc.'),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Mis en avant')
                        ->default(false)
                        ->helperText('Affiche ce cours en priorite sur la page catalogue et la page d\'accueil.')
                        ->columnSpan(2),
                ]),

            Section::make('Paiement & publication')
                ->columns(4)
                ->schema([
                    Forms\Components\Toggle::make('is_paid')
                        ->label('Payant')
                        ->default(true)
                        ->live()
                        ->disabled(fn () => !(auth()->user()?->can('courses.price') ?? false)),

                    Forms\Components\TextInput::make('price_cents')
                        ->label('Prix')
                        ->numeric()
                        ->prefix('DT')
                        ->step(0.01)
                        ->helperText('Exemple : 19.90 pour 19,90 DT')
                        ->afterStateHydrated(function ($state, $set) {
                            if (filled($state)) {
                                $set('price_cents', number_format($state / 100, 2, '.', ''));
                            }
                        })
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) round((float) $state * 100) : null)
                        ->disabled(fn ($get) =>
                            $get('is_paid') === false || !(auth()->user()?->can('courses.price') ?? false)
                        )
                        ->required(fn ($get) =>
                            $get('is_paid') === true && (auth()->user()?->can('courses.price') ?? false)
                        ),

                    Forms\Components\TextInput::make('currency')
                        ->label('Devise')
                        ->default('TND')
                        ->maxLength(3)
                        ->disabled(fn () => !(auth()->user()?->can('courses.price') ?? false)),

                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié')
                        ->default(false)
                        ->disabled(fn () => !(auth()->user()?->can('courses.publish') ?? false)),

                    Forms\Components\Toggle::make('has_preview')
                        ->label('Aperçu du pack')
                        ->default(false)
                        ->visible(fn ($get) => ($get('delivery_type') ?? 'course') === 'pack')
                        ->helperText('Si activé, le pack peut être consulté en mode aperçu sans accès complet.'),
                ]),
        ]);
    }
}