<?php

namespace App\Filament\RelationManagers;

use App\Services\CurrencyService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Per-market (per-currency) manual prices for a sellable record
 * (Course, Exercise). A row here overrides the auto-conversion of the
 * record's base price for that one currency.
 */
class MarketPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $title = 'Prix par marché';

    protected static ?string $icon = 'heroicon-o-globe-alt';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('currency')
                ->label('Devise')
                ->options(function () {
                    $base = strtoupper((string) ($this->getOwnerRecord()->currency ?? 'TND'));

                    return collect(CurrencyService::SUPPORTED)
                        ->reject(fn ($code) => $code === $base)
                        ->mapWithKeys(fn ($code) => [$code => $code.' ('.(CurrencyService::SYMBOLS[$code] ?? $code).')'])
                        ->all();
                })
                ->required()
                ->native(false)
                ->rules(function ($record) {
                    $owner = $this->getOwnerRecord();

                    $rule = \Illuminate\Validation\Rule::unique('product_prices', 'currency')
                        ->where('priceable_type', $owner->getMorphClass())
                        ->where('priceable_id', $owner->getKey());

                    if ($record?->id) {
                        $rule->ignore($record->id);
                    }

                    return [$rule];
                })
                ->validationMessages([
                    'unique' => 'Un prix est déjà défini pour cette devise.',
                ]),

            Forms\Components\TextInput::make('price_cents')
                ->label('Prix')
                ->numeric()
                ->step(0.01)
                ->required()
                ->helperText('Prix exact pour ce marché. Exemple : 149 pour 149 €.')
                ->afterStateHydrated(function ($state, $set) {
                    if (filled($state)) {
                        $set('price_cents', number_format($state / 100, 2, '.', ''));
                    }
                })
                ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) round((float) $state * 100) : null),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('currency')
            ->columns([
                TextColumn::make('currency')
                    ->label('Devise')
                    ->badge(),

                TextColumn::make('price_cents')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state, $record) => CurrencyService::format(((int) $state) / 100, $record->currency)),

                IconColumn::make('id')
                    ->label('Exact')
                    ->icon('heroicon-o-check-badge')
                    ->color('success'),

                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()->label('Ajouter un prix'),
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
        return (bool) (auth()->user()?->can('courses.price') ?? false);
    }
}
