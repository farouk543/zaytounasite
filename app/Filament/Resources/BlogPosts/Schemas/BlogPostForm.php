<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Models\BlogPost;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    private const LANGS = [
        'fr' => ['Français', ''],
        'ar' => ['العربية (arabe)', '_ar'],
        'en' => ['English (anglais)', '_en'],
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Group::make()
                        ->columnSpan(['default' => 3, 'lg' => 2])
                        ->schema([
                            Tabs::make('Contenu')
                                ->tabs(collect(self::LANGS)->map(fn ($lang, $code) => self::contentTab($code, ...$lang))->values()->all()),

                            Section::make('Référencement Google (SEO)')
                                ->description('Ce que Google affiche dans ses résultats. Laissez vide pour reprendre le titre et le résumé.')
                                ->icon('heroicon-o-magnifying-glass')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\TextInput::make('focus_keyword')
                                        ->label('Mot-clé principal')
                                        ->placeholder('ex : cours de maths en ligne bac tunisie')
                                        ->helperText('L’expression que les parents tapent dans Google. Placez-la dans le titre, le 1er paragraphe et un sous-titre.')
                                        ->maxLength(120),

                                    Tabs::make('SEO')
                                        ->tabs(collect(self::LANGS)->map(fn ($lang, $code) => Tab::make($lang[0])
                                            ->schema([
                                                Forms\Components\TextInput::make('meta_title'.$lang[1])
                                                    ->label('Titre Google')
                                                    ->maxLength(70)
                                                    ->live(onBlur: true)
                                                    ->hint(fn ($state) => mb_strlen((string) $state).' / 60 caractères')
                                                    ->helperText('Idéal : 50 à 60 caractères, mot-clé au début.'),
                                                Forms\Components\Textarea::make('meta_description'.$lang[1])
                                                    ->label('Description Google')
                                                    ->rows(2)
                                                    ->maxLength(320)
                                                    ->live(onBlur: true)
                                                    ->hint(fn ($state) => mb_strlen((string) $state).' / 155 caractères')
                                                    ->helperText('Idéal : 120 à 155 caractères, donne envie de cliquer.'),
                                            ]))->values()->all()),
                                ]),
                        ]),

                    Group::make()
                        ->columnSpan(['default' => 3, 'lg' => 1])
                        ->schema([
                            Section::make('Publication')
                                ->schema([
                                    Forms\Components\Select::make('status')
                                        ->label('Statut')
                                        ->options(['draft' => 'Brouillon', 'published' => 'Publié'])
                                        ->default('draft')
                                        ->required()
                                        ->native(false),

                                    Forms\Components\DateTimePicker::make('published_at')
                                        ->label('Date de publication')
                                        ->helperText('Heure de Tunis. Vide = maintenant. Une date future programme la publication.')
                                        ->timezone(config('marketing.timezone'))
                                        ->seconds(false),

                                    Forms\Components\CheckboxList::make('target_countries')
                                        ->label('Pays ciblés')
                                        ->options(collect(config('marketing.markets'))->map(fn ($m) => $m['flag'].' '.$m['name'])->all())
                                        ->columns(2)
                                        ->bulkToggleable()
                                        ->helperText('Nos 5 marchés prioritaires. Citez ces pays dans le texte (villes, programme, horaires) : l’article leur sera proposé en premier.'),

                                    Forms\Components\Select::make('blog_category_id')
                                        ->label('Catégorie')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')->label('Nom (FR)')->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug((string) $state))),
                                            Forms\Components\TextInput::make('slug')->required()->unique('blog_categories', 'slug'),
                                        ]),

                                    Forms\Components\Toggle::make('is_featured')
                                        ->label('Mettre à la une')
                                        ->helperText('Affiché en grand en tête du blog.'),

                                    Forms\Components\TextInput::make('slug')
                                        ->label('Adresse (URL)')
                                        ->prefix('/blog/')
                                        ->required()
                                        ->maxLength(160)
                                        ->alphaDash()
                                        ->unique(ignoreRecord: true)
                                        ->helperText('Généré depuis le titre FR. Courte, avec le mot-clé, sans accents. Évitez de la modifier après publication.'),
                                ]),

                            Section::make('Image de couverture')
                                ->schema([
                                    Forms\Components\FileUpload::make('cover_image')
                                        ->hiddenLabel()
                                        ->image()
                                        ->disk('public')
                                        ->directory('blog/covers')
                                        ->visibility('public')
                                        ->imageEditor()
                                        ->imageAspectRatio('1.91:1')
                                        ->automaticallyCropImagesToAspectRatio()
                                        ->maxSize(4096)
                                        ->helperText('Format conseillé 1200 × 630 px (partage Facebook/WhatsApp). Max 4 Mo.'),
                                ]),

                            Section::make('Statistiques')
                                ->visibleOn('edit')
                                ->schema([
                                    Forms\Components\Placeholder::make('views')
                                        ->label('Lectures')
                                        ->content(fn (?BlogPost $record) => number_format($record?->views_count ?? 0, 0, ',', ' ')),
                                    Forms\Components\Placeholder::make('langs')
                                        ->label('Langues rédigées')
                                        ->content(fn (?BlogPost $record) => $record ? strtoupper(implode(' · ', $record->availableLocales()) ?: '—') : '—'),
                                ]),
                        ]),
                ]),
        ]);
    }

    private static function contentTab(string $code, string $label, string $suffix): Tab
    {
        $isFr = $code === 'fr';

        $title = Forms\Components\TextInput::make('title'.$suffix)
            ->label('Titre')
            ->required($isFr)
            ->maxLength(190)
            ->extraInputAttributes($code === 'ar' ? ['dir' => 'rtl'] : []);

        if ($isFr) {
            $title->live(onBlur: true)
                ->afterStateUpdated(function ($get, $set, ?string $state, string $operation) {
                    if ($operation === 'create' || blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                });
        }

        return Tab::make($label)
            ->badge(fn ($get) => filled($get('title'.$suffix)) && filled($get('content'.$suffix)) ? '✓' : null)
            ->schema([
                $title,

                Forms\Components\Textarea::make('excerpt'.$suffix)
                    ->label('Résumé (chapeau)')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('2–3 phrases affichées dans la liste des articles.')
                    ->extraInputAttributes($code === 'ar' ? ['dir' => 'rtl'] : []),

                Forms\Components\RichEditor::make('content'.$suffix)
                    ->label('Contenu')
                    ->required($isFr)
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('blog/content')
                    ->fileAttachmentsVisibility('public')
                    ->extraInputAttributes(['style' => 'min-height: 24rem;'] + ($code === 'ar' ? ['dir' => 'rtl'] : []))
                    ->helperText($isFr
                        ? 'Structurez avec des sous-titres H2 / H3, des listes et des liens vers vos cours. Visez 800 mots minimum.'
                        : 'Laissez vide si l’article n’est pas traduit : il ne sera pas proposé dans cette langue.'),
            ]);
    }
}
