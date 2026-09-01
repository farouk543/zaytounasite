<?php

namespace App\Models\Concerns;

use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Adds per-market (per-currency) manual prices to a sellable model.
 *
 * The model keeps its base price in its own `price_cents` / `currency`
 * columns. Rows in `product_prices` override that base for a given
 * currency; anything not overridden is auto-converted by PriceResolver.
 */
trait HasMarketPrices
{
    public function prices(): MorphMany
    {
        return $this->morphMany(ProductPrice::class, 'priceable');
    }

    /**
     * Exact manual price for a currency, in cents, or null when none is set.
     */
    public function marketPriceCents(string $currency): ?int
    {
        $currency = strtoupper($currency);

        $row = $this->relationLoaded('prices')
            ? $this->prices->firstWhere('currency', $currency)
            : $this->prices()->where('currency', $currency)->first();

        return $row ? (int) $row->price_cents : null;
    }
}
