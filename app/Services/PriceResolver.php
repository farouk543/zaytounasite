<?php

namespace App\Services;

use App\Support\PriceQuote;
use Illuminate\Database\Eloquent\Model;

/**
 * Single entry point for "what does this product cost in the visitor's currency?".
 *
 * Order of precedence:
 *   1. an exact per-market price row (product_prices) → exact
 *   2. auto-conversion of the base price via CurrencyService → estimated
 *
 * Works with any model that uses the HasMarketPrices trait and exposes
 * `price_cents` + `currency` columns (Course, Exercise).
 */
class PriceResolver
{
    public function resolve(Model $product, ?string $currency = null): PriceQuote
    {
        $currency = $currency ? strtoupper($currency) : CurrencyService::current();

        if (! CurrencyService::isSupported($currency)) {
            $currency = 'TND';
        }

        $baseCents = (int) ($product->price_cents ?? 0);
        $baseCurrency = strtoupper((string) ($product->currency ?: 'TND'));

        // 1. Exact manual price for this market.
        if (method_exists($product, 'marketPriceCents')) {
            $exact = $product->marketPriceCents($currency);

            if ($exact !== null) {
                return new PriceQuote(
                    priceCents: $exact,
                    currency: $currency,
                    isEstimated: false,
                    baseCents: $baseCents,
                    baseCurrency: $baseCurrency,
                );
            }
        }

        // Base currency itself: no conversion, treat as exact.
        if ($currency === $baseCurrency || $baseCents === 0) {
            return new PriceQuote(
                priceCents: $baseCents,
                currency: $currency === $baseCurrency ? $currency : $baseCurrency,
                isEstimated: false,
                baseCents: $baseCents,
                baseCurrency: $baseCurrency,
            );
        }

        // 2. Auto-conversion fallback.
        $converted = CurrencyService::convert($baseCents / 100, $baseCurrency, $currency);
        $rounded = CurrencyService::roundForMarket($converted, $currency);

        return new PriceQuote(
            priceCents: $rounded * 100,
            currency: $currency,
            isEstimated: true,
            baseCents: $baseCents,
            baseCurrency: $baseCurrency,
        );
    }

    /**
     * Resolve a collection of products at once. Eager-loads `prices` when
     * possible so each resolve() stays a single in-memory lookup.
     *
     * @return array<int|string, PriceQuote> keyed by model primary key
     */
    public function resolveMany(iterable $products, ?string $currency = null): array
    {
        $out = [];

        foreach ($products as $product) {
            if ($product instanceof Model
                && ! $product->relationLoaded('prices')
                && method_exists($product, 'prices')) {
                $product->load('prices');
            }

            $out[$product->getKey()] = $this->resolve($product, $currency);
        }

        return $out;
    }
}
