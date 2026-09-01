<?php

namespace App\Support;

use App\Services\CurrencyService;

/**
 * Immutable result of resolving a product price for a given market.
 */
class PriceQuote
{
    public function __construct(
        public readonly int $priceCents,
        public readonly string $currency,
        public readonly bool $isEstimated,
        public readonly int $baseCents,
        public readonly string $baseCurrency,
    ) {}

    public function symbol(): string
    {
        return CurrencyService::SYMBOLS[$this->currency] ?? $this->currency;
    }

    public function amount(): float
    {
        return $this->priceCents / 100;
    }

    /** e.g. "149 €" */
    public function formatted(): string
    {
        return CurrencyService::format($this->amount(), $this->currency);
    }

    /** e.g. "≈ 149 €" when the price is an auto-conversion */
    public function formattedWithHint(): string
    {
        return ($this->isEstimated ? '≈ ' : '').$this->formatted();
    }

    public function toArray(): array
    {
        return [
            'price_cents' => $this->priceCents,
            'currency' => $this->currency,
            'is_estimated' => $this->isEstimated,
            'base_cents' => $this->baseCents,
            'base_currency' => $this->baseCurrency,
            'formatted' => $this->formatted(),
        ];
    }
}
