<?php

use App\Models\BlogPost;
use App\Services\Blog\ArticleRenderer;
use Database\Seeders\BlogArticlesSeeder;
use Illuminate\Support\Facades\Storage;

it('seeds the 11 scheduled articles once, with covers, markets and translations', function () {
    Storage::fake('public');

    $this->seed(BlogArticlesSeeder::class);

    expect(BlogPost::count())->toBe(11);

    $bac = BlogPost::where('slug', 'reviser-bac-tunisie-2027')->sole();
    expect($bac->availableLocales())->toBe(['fr', 'ar'])
        ->and($bac->published_at->toDateTimeString())->toBe('2026-11-04 07:00:00')
        ->and($bac->focus_keyword)->not->toBeEmpty()
        ->and($bac->target_countries)->toBe(['TN']);

    // Chacun des 5 marchés prioritaires a au moins un article qui lui est dédié.
    foreach (['TN', 'FR', 'SA', 'QA', 'CA'] as $cc) {
        expect(BlogPost::all()->filter(fn ($p) => $p->targetsCountry($cc))->count())->toBeGreaterThan(2);
    }
    Storage::disk('public')->assertExists('blog/covers/reviser-bac-tunisie-2027.jpg');

    expect(BlogPost::where('slug', 'cours-coran-en-ligne-enfant')->sole()->availableLocales())->toBe(['fr', 'ar', 'en']);

    // Relancer le seeder ne duplique ni n'écrase les retouches faites dans le back-office.
    $bac->update(['title' => 'Titre retouché']);
    $this->seed(BlogArticlesSeeder::class);
    expect(BlogPost::count())->toBe(11)->and($bac->fresh()->title)->toBe('Titre retouché');
});

it('keeps scheduled articles off the site until their date', function () {
    Storage::fake('public');
    $this->travelTo('2026-11-03 12:00:00');
    $this->seed(BlogArticlesSeeder::class);

    $this->get('/blog/reviser-bac-tunisie-2027')->assertNotFound();

    $this->travelTo('2026-11-04 09:00:00');
    $this->get('/blog')->assertOk()->assertSee('Bac 2027 en Tunisie');
    $this->get('/blog/reviser-bac-tunisie-2027')->assertOk()
        ->assertSee('FAQPage', false)
        ->assertSee('zb-faqItem', false)
        ->assertSee('zb-inlineCta', false);
    $this->get('/blog/concours-6eme-9eme-tunisie')->assertNotFound();
});

it('renders the editor html as a magazine article', function () {
    $html = '<p>Intro</p><h2>Première partie</h2><p>Voir le <a target="_blank" rel="noopener noreferrer nofollow" href="/catalog">catalogue</a> '
        .'ou <a href="https://example.org/x">un site</a>.</p><blockquote><p>Retenir ceci</p></blockquote>'
        .'<h2>Deuxième</h2><table><tbody><tr><th><p>A</p></th></tr></tbody></table><h2>Troisième</h2><p>Fin</p>'
        .'<h2>Questions fréquentes</h2><h3>Une question ?</h3><p>Une réponse.</p><h3>Autre ?</h3><p>Oui.</p>';

    $out = app(ArticleRenderer::class)->render($html, 'À retenir');

    expect($out['toc'])->toHaveCount(3)
        ->and($out['toc'][0])->toBe(['id' => 'premiere-partie', 'text' => 'Première partie'])
        ->and($out['faq'])->toBe([['q' => 'Une question ?', 'a' => '<p>Une réponse.</p>'], ['q' => 'Autre ?', 'a' => '<p>Oui.</p>']])
        ->and($out['html'])->toContain('class="zb-lead"')
        ->toContain('id="premiere-partie"')
        ->toContain('class="zb-callout" data-label="À retenir"')
        ->toContain('<div class="zb-tableWrap"><table>')
        ->toContain('<th>A</th>')
        ->toContain(ArticleRenderer::INLINE_CTA)
        ->toContain('href="'.url('/catalog').'"')
        ->toContain('rel="noopener noreferrer"')
        ->not->toContain('nofollow')
        ->not->toContain('Questions fréquentes');
});

it('announces regional hreflang variants for the priority markets', function () {
    $this->get('/faq')->assertOk()
        ->assertSee('hreflang="fr-TN"', false)
        ->assertSee('hreflang="fr-CA"', false)
        ->assertSee('hreflang="ar-SA"', false)
        ->assertSee('hreflang="ar-QA"', false)
        ->assertSee('hreflang="x-default"', false);
});

it('shows a "for your country" selection to visitors from a priority market', function () {
    Storage::fake('public');
    $this->travelTo('2027-02-01 12:00:00');
    $this->seed(BlogArticlesSeeder::class);

    $this->withHeaders(['CF-IPCountry' => 'CA'])->get('/blog')->assertOk()
        ->assertSee('class="zb-forYou"', false)
        ->assertSee('Familles au Canada');

    $this->withHeaders(['CF-IPCountry' => 'DE'])->get('/blog')->assertOk()
        ->assertDontSee('class="zb-forYou"', false);
});
