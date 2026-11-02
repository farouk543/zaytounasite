<?php

namespace App\Http\Controllers;

use App\Services\SitemapBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(SitemapBuilder $builder): Response
    {
        $xml = Cache::remember('seo.sitemap.xml', now()->addHour(), fn () => view('seo.sitemap', [
            'entries' => $builder->entries(),
        ])->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
        ];

        if (app()->isProduction()) {
            array_push(
                $lines,
                'Disallow: /admin',
                'Disallow: /livewire',
                'Disallow: /cart',
                'Disallow: /checkout',
                'Disallow: /dashboard',
                'Disallow: /profile',
                'Disallow: /my-courses',
                'Disallow: /student/',
                'Disallow: /quiz',
                'Disallow: /quiz-attempt',
                'Disallow: /exercise',
                'Disallow: /lang/',
                'Disallow: /devise/',
                'Disallow: /_health/',
                'Disallow: /t/',
                'Disallow: /*?*q=',
                '',
                'Sitemap: '.route('seo.sitemap'),
            );
        } else {
            // Hors production (local, staging) : on interdit l'indexation.
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
