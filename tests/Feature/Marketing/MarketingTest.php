<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ClickEvent;
use App\Models\PageView;
use App\Models\User;
use Spatie\Permission\Models\Role;

const HUMAN_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

function makeAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP'); // double authentification configurée

    return $admin;
}

function makePost(array $attrs = []): BlogPost
{
    return BlogPost::create(array_merge([
        'slug' => 'reviser-le-bac',
        'title' => 'Réviser le bac',
        'content' => '<h2>Plan</h2><p>Commencez tôt.</p>',
        'status' => 'published',
    ], $attrs));
}

it('records a page view for a human visitor with source and device', function () {
    $this->withHeaders(['User-Agent' => HUMAN_UA, 'Referer' => 'https://www.google.com/', 'CF-IPCountry' => 'TN'])
        ->get('/faq')->assertOk();

    $view = PageView::sole();
    expect($view->path)->toBe('/faq')
        ->and($view->source)->toBe('Google')
        ->and($view->device)->toBe('mobile')
        ->and($view->country)->toBe('TN')
        ->and($view->title)->toContain('FAQ');
});

it('does not record bots, admins or the admin panel', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])->get('/faq');
    $this->actingAs(makeAdmin())->withHeaders(['User-Agent' => HUMAN_UA])->get('/faq');

    expect(PageView::count())->toBe(0);
});

it('stores clicks sent by the tracker script', function () {
    $this->withHeaders(['User-Agent' => HUMAN_UA])
        ->post('/t/c', ['p' => '/', 'l' => '  Commencer  ', 'k' => 'link', 'h' => url('/register').'?x=1'])
        ->assertNoContent();

    $click = ClickEvent::sole();
    expect($click->label)->toBe('Commencer')->and($click->target)->toBe('/register');
});

it('shows published posts, hides drafts from visitors but not from admins', function () {
    $cat = BlogCategory::create(['name' => 'Examens', 'slug' => 'examens']);
    $post = makePost(['blog_category_id' => $cat->id, 'title_ar' => 'مراجعة', 'content_ar' => '<p>نص</p>']);
    makePost(['slug' => 'brouillon', 'title' => 'Brouillon', 'status' => 'draft']);

    expect($post->published_at)->not->toBeNull();

    $this->get('/blog')->assertOk()->assertSee('Réviser le bac')->assertDontSee('Brouillon');
    $this->get('/blog/categorie/examens')->assertOk()->assertSee('Réviser le bac');
    $this->get('/blog/reviser-le-bac')->assertOk()
        ->assertSee('BlogPosting', false)
        ->assertSee('hreflang="ar"', false)
        ->assertDontSee('hreflang="en"', false);
    $this->get('/blog/brouillon')->assertNotFound();
    $this->actingAs(makeAdmin())->get('/blog/brouillon')->assertOk()->assertSee('noindex', false);
});

it('lists public pages and published posts in the sitemap', function () {
    makePost();
    makePost(['slug' => 'brouillon', 'status' => 'draft']);

    $this->get('/sitemap.xml')->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('/blog/reviser-le-bac', false)
        ->assertSee('/regimes/tunisia', false)
        ->assertDontSee('/blog/brouillon', false);
});

it('switches language from the url', function () {
    $this->get('/faq?lang=ar')->assertOk()->assertSee('<html lang="ar" dir="rtl">', false);
});

it('renders the admin analytics and blog pages with data', function () {
    makePost();
    PageView::create(['visitor_hash' => str_repeat('a', 64), 'path' => '/blog/reviser-le-bac', 'route_name' => 'blog.show',
        'title' => 'Réviser le bac | Zaytouna Academy', 'source' => 'Facebook', 'country' => 'FR', 'device' => 'mobile',
        'browser' => 'Chrome', 'os' => 'Android', 'locale' => 'fr']);
    ClickEvent::create(['visitor_hash' => str_repeat('a', 64), 'page_path' => '/', 'label' => 'Commencer', 'kind' => 'link']);

    $this->actingAs(makeAdmin());
    foreach (['today', '7d', '30d', 'month', 'last_month', '90d', '12m'] as $period) {
        $this->get('/admin/statistiques?period='.$period)->assertOk()
            ->assertSee('Évolution mois par mois')->assertSee('Commencer')->assertSee('Facebook');
    }
    $this->get('/admin/blog/articles')->assertOk()->assertSee('Réviser le bac');
    $this->get('/admin/blog/articles/create')->assertOk();
    $this->get('/admin/blog/categories')->assertOk();
});
