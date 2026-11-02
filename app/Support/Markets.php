<?php

namespace App\Support;

/** Accès aux marchés prioritaires définis dans config/marketing.php. */
class Markets
{
    /** @return array<string, array{name: string, flag: string, hreflang: array, timezone: string}> */
    public static function all(): array
    {
        return config('marketing.markets', []);
    }

    public static function has(?string $country): bool
    {
        return $country !== null && array_key_exists(strtoupper($country), self::all());
    }

    /** Nom du pays dans la langue demandée (intl si disponible, sinon nom français de la config). */
    public static function name(string $country, ?string $locale = null): string
    {
        $country = strtoupper($country);
        $locale ??= app()->getLocale();

        if (class_exists(\Locale::class)) {
            $name = \Locale::getDisplayRegion('-'.$country, $locale);
            if ($name && $name !== $country) {
                return $name;
            }
        }

        return self::all()[$country]['name'] ?? $country;
    }

    /** Drapeau en image (les émojis de drapeaux ne s'affichent pas sous Windows). */
    public static function flagHtml(string $country, int $height = 14): string
    {
        $cc = strtolower($country);

        if (! is_file(public_path("images/flags/{$cc}.svg"))) {
            return e(self::flag($country));
        }

        $width = (int) round($height * 4 / 3);

        return '<img src="'.e(asset("images/flags/{$cc}.svg")).'" alt="" width="'.$width.'" height="'.$height.'"'
            .' style="display:inline-block;vertical-align:-2px;border-radius:2px;box-shadow:0 0 0 1px rgba(0,0,0,.08)">';
    }

    public static function flag(string $country): string
    {
        return self::all()[strtoupper($country)]['flag'] ?? '🌐';
    }
}
