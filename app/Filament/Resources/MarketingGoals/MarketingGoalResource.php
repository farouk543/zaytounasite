<?php

namespace App\Filament\Resources\MarketingGoals;

use App\Filament\Resources\MarketingGoals\Pages\ManageMarketingGoals;
use App\Models\MarketingGoal;
use App\Services\Analytics\GoalTracker;
use App\Support\Markets;
use BackedEnum;
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
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

/**
 * Objectifs marketing mensuels : la cible, le réalisé (calculé en direct) et le statut.
 * La note globale et les recommandations s'affichent dans « Statistiques du site ».
 */
class MarketingGoalResource extends Resource
{
    protected static ?string $model = MarketingGoal::class;

    protected static ?string $slug = 'objectifs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing & Blog';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Objectifs marketing';

    protected static ?string $modelLabel = 'objectif';

    protected static ?string $pluralModelLabel = 'Objectifs marketing';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function countryOptions(): array
    {
        return collect(Markets::all())->map(fn ($m) => $m['flag'].' '.$m['name'])->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->schema([
            Forms\Components\Select::make('month')
                ->label('Mois')
                ->options(MarketingGoal::monthOptions(6, 12))
                ->default(now(config('marketing.timezone'))->format('Y-m'))
                ->required()
                ->native(false),

            Forms\Components\Select::make('metric')
                ->label('Indicateur')
                ->options(collect(MarketingGoal::METRICS)->map(fn ($m) => $m['label'])->all())
                ->required()
                ->live()
                ->native(false)
                ->helperText(fn ($get) => MarketingGoal::METRICS[$get('metric')]['help'] ?? null),

            Forms\Components\Select::make('country')
                ->label('Pays')
                ->options(self::countryOptions())
                ->placeholder('🌍 Tous pays confondus')
                ->visible(fn ($get) => MarketingGoal::METRICS[$get('metric')]['country'] ?? true)
                ->native(false)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $get) => $rule
                    ->where('month', $get('month'))->where('metric', $get('metric')))
                ->validationMessages(['unique' => 'Un objectif existe déjà pour ce mois, cet indicateur et ce pays.']),

            Forms\Components\TextInput::make('target')
                ->label('Objectif')
                ->numeric()
                ->minValue(0)
                ->required()
                ->suffix(fn ($get) => MarketingGoal::METRICS[$get('metric')]['unit'] ?? null),

            Forms\Components\Textarea::make('notes')
                ->label('Notes / actions prévues')
                ->rows(3)
                ->columnSpanFull()
                ->placeholder('Ex. : 2 articles ciblés Canada, campagne Facebook Montréal du 10 au 20.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        $tracker = app(GoalTracker::class);
        $row = fn (MarketingGoal $g) => $tracker->progress($g->month)->first(fn ($r) => $r->goal->is($g));

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->label('Mois')
                    ->formatStateUsing(fn (MarketingGoal $g) => $g->monthLabel())
                    ->sortable(),

                Tables\Columns\TextColumn::make('metric')
                    ->label('Indicateur')
                    ->formatStateUsing(fn (MarketingGoal $g) => $g->metricLabel())
                    ->description(fn (MarketingGoal $g) => $g->notes ? \Illuminate\Support\Str::limit($g->notes, 70) : null),

                Tables\Columns\TextColumn::make('country')
                    ->label('Pays')
                    ->formatStateUsing(fn (?string $state) => $state ? Markets::flagHtml($state).' '.e(Markets::name($state, 'fr')) : '🌍 Tous')
                    ->html()
                    ->placeholder('🌍 Tous'),

                Tables\Columns\TextColumn::make('target')
                    ->label('Objectif')
                    ->formatStateUsing(fn (MarketingGoal $g) => rtrim(rtrim(number_format($g->target, 1, ',', ' '), '0'), ',').$g->unit())
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('actual')
                    ->label('Réalisé')
                    ->state(fn (MarketingGoal $g) => rtrim(rtrim(number_format($row($g)->actual ?? 0, 1, ',', ' '), '0'), ',').$g->unit())
                    ->weight('bold')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Avancement')
                    ->state(fn (MarketingGoal $g) => ($row($g)->percent ?? 0).' %')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->state(fn (MarketingGoal $g) => $row($g)->status ?? '—')
                    ->color(fn (MarketingGoal $g) => $row($g)->color ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label('Mois')
                    ->options(fn () => MarketingGoal::query()->distinct()->orderByDesc('month')->pluck('month')
                        ->mapWithKeys(fn ($m) => [$m => (new MarketingGoal(['month' => $m]))->monthLabel()])->all())
                    ->default(now(config('marketing.timezone'))->format('Y-m')),
                Tables\Filters\SelectFilter::make('country')
                    ->label('Pays')
                    ->options(self::countryOptions()),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('month', 'desc')
            ->emptyStateHeading('Aucun objectif pour ce mois')
            ->emptyStateDescription('Utilisez « Préparer le mois suivant » pour reprendre les objectifs d’un mois en les augmentant.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMarketingGoals::route('/'),
        ];
    }
}
