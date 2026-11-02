<?php

namespace App\Support;

/**
 * Liens hreflang d'une page : une entrée par langue du site, plus les variantes
 * régionales des marchés prioritaires (config/marketing.php), qui pointent vers la même
 * version linguistique. Ex. fr, fr-TN, fr-FR, fr-CA → la version française.
 */
class Hreflang
{
    /**
     * @param  array<int, string>  $locales  langues réellement disponibles (fr, ar, en)
     * @return array<string, string> code hreflang => URL
     */
    public static function alternates(string $baseUrl, array $locales): array
    {
        $out = [];

        foreach ($locales as $locale) {
            $href = $locale === 'fr' ? $baseUrl : $baseUrl.'?lang='.$locale;
            $out[$locale] = $href;

            foreach (config('marketing.markets', []) as $market) {
                if (isset($market['hreflang'][$locale])) {
                    $out[$market['hreflang'][$locale]] = $href;
                }
            }
        }

        return $out;
    }
}
