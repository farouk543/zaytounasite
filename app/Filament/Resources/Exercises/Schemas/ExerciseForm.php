<?php

namespace App\Filament\Resources\Exercises\Schemas;

use App\Models\Course;
use App\Models\CoursePackItem;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Placeholder::make('create_notice')
                ->label('')
                ->content('💡 Enregistrez d\'abord l\'exercice. Vous pourrez ensuite ajouter les items (questions/tâches) depuis la page de modification.')
                ->visible(fn (string $operation) => $operation === 'create'),

            Section::make('Type d\'exercice')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('exercise_type')
                        ->label('Type')
                        ->options([
                            'interactive'       => '🎯 Interactif (questions / réponses)',
                            'pdf'               => '📄 PDF (document uploadé)',
                            'audio_interactive' => '🎧 Audio + questions',
                            'video_interactive' => '🎬 Vidéo MP4 + questions',
                        ])
                        ->default('interactive')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Interactif : questions classiques. PDF : document à consulter. Audio : écouter puis répondre. Vidéo : regarder puis répondre.'),

                    Forms\Components\FileUpload::make('pdf_path')
                        ->label('Fichier PDF')
                        ->disk('public')
                        ->directory('exercises/pdf')
                        ->visibility('public')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(20480)
                        ->downloadable()
                        ->openable()
                        ->previewable(false)
                        ->visible(fn ($get) => $get('exercise_type') === 'pdf')
                        ->required(fn ($get) => $get('exercise_type') === 'pdf')
                        ->helperText('PDF uniquement, 20 Mo max.'),

                    Forms\Components\FileUpload::make('audio_path')
                        ->label('Fichier audio')
                        ->disk('public')
                        ->directory('exercises/audios')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'audio/mpeg',
                            'audio/mp3',
                            'audio/wav',
                            'audio/ogg',
                            'audio/webm',
                            'audio/x-m4a',
                            'audio/aac',
                        ])
                        ->maxSize(20480)
                        ->downloadable(false)
                        ->openable()
                        ->visible(fn ($get) => $get('exercise_type') === 'audio_interactive')
                        ->required(fn ($get) => $get('exercise_type') === 'audio_interactive')
                        ->helperText('Audio uniquement : MP3, WAV, OGG, WEBM, M4A ou AAC. 20 Mo max.'),

                    Forms\Components\FileUpload::make('video_path')
                        ->label('Vidéo MP4')
                        ->disk('public')
                        ->directory('exercises/videos')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'video/mp4',
                        ])
                        ->maxSize(102400)
                        ->downloadable(false)
                        ->openable()
                        ->visible(fn ($get) => $get('exercise_type') === 'video_interactive')
                        ->required(fn ($get) => $get('exercise_type') === 'video_interactive')
                        ->helperText('Vidéo MP4 avec audio. 100 Mo max.'),

                    Forms\Components\Placeholder::make('pdf_preview')
                        ->label('Prévisualisation PDF')
                        ->content(function ($get) {
                            $state = $get('pdf_path');

                            if (empty($state)) {
                                return new HtmlString('<p style="color:rgba(100,116,139,1);font-size:.85rem;">Le PDF sera prévisualisé ici après enregistrement.</p>');
                            }

                            if ($state instanceof TemporaryUploadedFile) {
                                return new HtmlString('<p style="color:rgba(100,116,139,1);font-size:.85rem;">PDF uploadé. La prévisualisation sera disponible après l’enregistrement.</p>');
                            }

                            if (is_array($state)) {
                                $file = collect($state)->first();

                                if ($file instanceof TemporaryUploadedFile) {
                                    return new HtmlString('<p style="color:rgba(100,116,139,1);font-size:.85rem;">PDF uploadé. La prévisualisation sera disponible après l’enregistrement.</p>');
                                }

                                if (is_string($file)) {
                                    $url = Storage::disk('public')->url($file);
                                } else {
                                    return new HtmlString('<p style="color:#ef4444;font-size:.85rem;">Impossible de prévisualiser ce PDF avant l’enregistrement.</p>');
                                }
                            } elseif (is_string($state)) {
                                $url = Storage::disk('public')->url($state);
                            } else {
                                return new HtmlString('<p style="color:#ef4444;font-size:.85rem;">Format PDF non reconnu.</p>');
                            }

                            return new HtmlString(
                                '<div style="border-radius:12px;overflow:hidden;border:1px solid rgba(0,0,0,.1);">'
                                . '<embed src="' . e($url) . '#toolbar=1&navpanes=0&view=FitH" type="application/pdf" style="width:100%;height:560px;display:block;" />'
                                . '</div>'
                                . '<a href="' . e($url) . '" target="_blank" style="display:inline-block;margin-top:.5rem;font-size:.8rem;color:#3b82f6;">⬡ Ouvrir dans un nouvel onglet</a>'
                            );
                        })
                        ->visible(fn ($get) => $get('exercise_type') === 'pdf')
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('audio_preview')
                        ->label('Prévisualisation audio')
                        ->content(function ($get) {
                            $state = $get('audio_path');

                            if (empty($state)) {
                                return new HtmlString('<p style="color:rgba(100,116,139,1);font-size:.85rem;">L’audio sera prévisualisé ici après sélection ou enregistrement.</p>');
                            }

                            $url = self::resolveTemporaryOrStoredFileUrl($state);

                            if (! $url) {
                                return new HtmlString('<p style="color:#ef4444;font-size:.85rem;">Impossible de prévisualiser cet audio avant l’enregistrement.</p>');
                            }

                            return new HtmlString(
                                '<div style="padding:16px;border-radius:14px;border:1px solid rgba(0,0,0,.1);background:#f8fafc;">'
                                . '<audio controls preload="metadata" controlsList="nodownload" style="width:100%;">'
                                . '<source src="' . e($url) . '">'
                                . 'Votre navigateur ne supporte pas l’audio.'
                                . '</audio>'
                                . '</div>'
                            );
                        })
                        ->visible(fn ($get) => $get('exercise_type') === 'audio_interactive')
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('video_preview')
                        ->label('Prévisualisation vidéo')
                        ->content(function ($get) {
                            $state = $get('video_path');

                            if (empty($state)) {
                                return new HtmlString('<p style="color:rgba(100,116,139,1);font-size:.85rem;">La vidéo sera prévisualisée ici après sélection ou enregistrement.</p>');
                            }

                            $url = self::resolveTemporaryOrStoredFileUrl($state);

                            if (! $url) {
                                return new HtmlString('<p style="color:#ef4444;font-size:.85rem;">Impossible de prévisualiser cette vidéo avant l’enregistrement.</p>');
                            }

                            return new HtmlString(
                                '<div style="padding:16px;border-radius:14px;border:1px solid rgba(0,0,0,.1);background:#f8fafc;">'
                                . '<video controls preload="metadata" controlsList="nodownload" style="width:100%;max-height:420px;border-radius:12px;background:#000;">'
                                . '<source src="' . e($url) . '" type="video/mp4">'
                                . 'Votre navigateur ne supporte pas la vidéo.'
                                . '</video>'
                                . '</div>'
                            );
                        })
                        ->visible(fn ($get) => $get('exercise_type') === 'video_interactive')
                        ->columnSpanFull(),
                ]),

            Section::make('Affectation')
                ->description('Un exercice est lié soit à un cours simple, soit à un item de pack de type exercice. Laissez vide pour vendre l’exercice seul dans le catalogue.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('course_id')
                        ->label('Cours simple')
                        ->options(fn () => Course::query()
                            ->whereIn('delivery_type', ['course', 'series', 'exam'])
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
                        ->label('Item de pack (type exercice)')
                        ->options(fn () => CoursePackItem::query()
                            ->where('item_type', 'exercise')
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
                        ->helperText('Crée d\'abord l\'item de pack de type « Exercice » dans le cours pack.'),
                ]),

            Section::make('Contenu')
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
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('instructions')
                        ->label('Consignes (FR)')
                        ->rows(3)
                        ->helperText('Instructions affichées avant le démarrage de l\'exercice.')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('instructions_ar')
                        ->label('Consignes (AR)')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Paiement')
                ->description('Utilisé seulement pour les exercices indépendants vendus seuls dans le catalogue.')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_paid')
                        ->label('Exercice payant')
                        ->default(true)
                        ->live()
                        ->helperText('Si activé, l’étudiant doit acheter l’exercice avant d’y accéder.'),

                    Forms\Components\TextInput::make('price_cents')
                        ->label('Prix')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('centimes')
                        ->visible(fn ($get) => (bool) $get('is_paid'))
                        ->required(fn ($get) => (bool) $get('is_paid'))
                        ->helperText('Exemple : 1990 = 19.90 TND.'),

                    Forms\Components\Select::make('currency')
                        ->label('Devise')
                        ->options([
                            'TND' => 'TND',
                            'EUR' => 'EUR',
                            'CAD' => 'CAD',
                            'USD' => 'USD',
                        ])
                        ->default('TND')
                        ->visible(fn ($get) => (bool) $get('is_paid'))
                        ->required(fn ($get) => (bool) $get('is_paid')),
                ]),

            Section::make('Paramètres pédagogiques')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('difficulty')
                        ->label('Difficulté')
                        ->options([
                            'easy'   => 'Facile',
                            'medium' => 'Moyen',
                            'hard'   => 'Difficile',
                        ])
                        ->native(false)
                        ->nullable()
                        ->placeholder('Non spécifiée'),

                    Forms\Components\TextInput::make('estimated_duration_minutes')
                        ->label('Durée estimée (min)')
                        ->numeric()
                        ->nullable()
                        ->minValue(1)
                        ->placeholder('Ex : 15'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié')
                        ->default(false),

                    Forms\Components\Toggle::make('show_correction_immediately')
                        ->label('Correction immédiate')
                        ->default(true)
                        ->helperText('Affiche la bonne réponse dès que l\'étudiant valide.'),

                    Forms\Components\Toggle::make('allow_retry')
                        ->label('Autoriser à recommencer')
                        ->default(true),
                ]),
        ]);
    }

    private static function resolveTemporaryOrStoredFileUrl(mixed $state): ?string
    {
        if ($state instanceof TemporaryUploadedFile) {
            return $state->temporaryUrl();
        }

        if (is_array($state)) {
            $file = collect($state)->first();

            if ($file instanceof TemporaryUploadedFile) {
                return $file->temporaryUrl();
            }

            if (is_string($file)) {
                return Storage::disk('public')->url($file);
            }

            return null;
        }

        if (is_string($state)) {
            return Storage::disk('public')->url($state);
        }

        return null;
    }
}