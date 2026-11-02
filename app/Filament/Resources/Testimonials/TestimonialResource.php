<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use App\Support\Markets;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Avis de parents : saisis ici ou reçus par le formulaire public /avis (à valider avant publication).
 */
class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $slug = 'temoignages';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing & Blog';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Témoignages';

    protected static ?string $modelLabel = 'témoignage';

    protected static ?string $pluralModelLabel = 'Témoignages';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Testimonial::where('is_published', false)->where('source', 'site')->count();

        return $pending ? (string) $pending : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Avis reçus du site, en attente de validation';
    }

    /** Nos 5 marchés d'abord, puis les autres pays fréquents. */
    public static function countryOptions(): array
    {
        $codes = array_merge(array_keys(Markets::all()), ['BE', 'CH', 'AE', 'KW', 'MA', 'DZ', 'LY', 'DE', 'IT', 'GB', 'US']);

        return collect($codes)->mapWithKeys(fn ($cc) => [$cc => Markets::name($cc, 'fr')])->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->schema([
            Forms\Components\TextInput::make('author_name')
                ->label('Nom affiché')
                ->helperText('Prénom + initiale (« Samia B. ») avec l’accord du parent.')
                ->required()
                ->maxLength(80),
            Forms\Components\TextInput::make('author_role')
                ->label('Qui est-ce ?')
                ->placeholder('Maman de Yasmine, 3e année')
                ->maxLength(120),
            Forms\Components\TextInput::make('city')->label('Ville')->maxLength(80),
            Forms\Components\Select::make('country')
                ->label('Pays')
                ->options(self::countryOptions())
                ->searchable()
                ->native(false),
            Forms\Components\Select::make('program')
                ->label('Programme')
                ->options(Testimonial::PROGRAMS)
                ->default('general')
                ->required()
                ->native(false),
            Forms\Components\Select::make('rating')
                ->label('Note')
                ->options([5 => '★★★★★ (5)', 4 => '★★★★☆ (4)', 3 => '★★★☆☆ (3)', 2 => '★★☆☆☆ (2)', 1 => '★☆☆☆☆ (1)'])
                ->default(5)
                ->required()
                ->native(false),
            Forms\Components\Textarea::make('quote')
                ->label('Avis (français ou langue d’origine)')
                ->rows(4)
                ->required()
                ->maxLength(1200)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('quote_ar')->label('Traduction arabe (facultatif)')->rows(3)->maxLength(1200),
            Forms\Components\Textarea::make('quote_en')->label('Traduction anglaise (facultatif)')->rows(3)->maxLength(1200),
            Forms\Components\DatePicker::make('received_at')->label('Reçu le')->default(now())->native(false),
            Forms\Components\TextInput::make('contact')->label('Contact (jamais affiché)')->maxLength(150),
            Forms\Components\Toggle::make('is_published')
                ->label('Publié sur le site')
                ->helperText('Ne publiez que des avis réels, avec l’accord de leur auteur.'),
            Forms\Components\Toggle::make('is_featured')->label('Mettre en avant (accueil)'),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('author_name')
                    ->label('Auteur')
                    ->searchable()
                    ->description(fn (Testimonial $t) => $t->author_role),
                Tables\Columns\TextColumn::make('country')
                    ->label('Pays')
                    ->formatStateUsing(fn (?string $state) => $state
                        ? (Markets::has($state) ? Markets::flagHtml($state).' ' : '').e(Markets::name($state, 'fr'))
                        : '—')
                    ->html(),
                Tables\Columns\TextColumn::make('program')
                    ->label('Programme')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Testimonial::PROGRAMS[$state] ?? $state),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Note')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state).str_repeat('☆', 5 - (int) $state))
                    ->color('warning'),
                Tables\Columns\TextColumn::make('quote')->label('Avis')->limit(80)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origine')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'site' => 'Formulaire du site',
                        'plateforme' => 'Plateforme (élève)',
                        default => 'Back-office',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'site' => 'info',
                        'plateforme' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('is_published')->label('Publié'),
                Tables\Columns\ToggleColumn::make('is_featured')->label('En avant'),
                Tables\Columns\TextColumn::make('received_at')->label('Reçu le')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Publié'),
                Tables\Filters\SelectFilter::make('program')->label('Programme')->options(Testimonial::PROGRAMS),
                Tables\Filters\SelectFilter::make('country')->label('Pays')->options(self::countryOptions()),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publier')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Testimonial $t) => ! $t->is_published)
                    ->action(fn (Testimonial $t) => $t->update(['is_published' => true])),
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun témoignage pour l’instant')
            ->emptyStateDescription('Envoyez le lien « Laisser un avis » aux parents satisfaits, ou saisissez ici un avis reçu par WhatsApp (avec leur accord).');
    }

    public static function getPages(): array
    {
        return ['index' => ManageTestimonials::route('/')];
    }
}
