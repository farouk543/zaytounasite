<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Analytics\VisitorTracker;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request, VisitorTracker $tracker, ?string $categorySlug = null)
    {
        $category = $categorySlug
            ? BlogCategory::where('slug', $categorySlug)->where('is_active', true)->firstOrFail()
            : null;

        $q = trim((string) $request->query('q', ''));

        $posts = BlogPost::query()
            ->published()
            ->with('category')
            ->when($category, fn ($query) => $query->where('blog_category_id', $category->id))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(fn ($w) => $w
                    ->where('title', 'like', $like)->orWhere('title_ar', 'like', $like)->orWhere('title_en', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)->orWhere('excerpt_ar', 'like', $like)->orWhere('excerpt_en', 'like', $like)
                    ->orWhere('focus_keyword', 'like', $like));
            })
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::query()
            ->where('is_active', true)
            ->whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get();

        // « Pour votre pays » : articles visant le pays du visiteur (marchés prioritaires).
        $country = $tracker->country($request);
        $market = $country ? config("marketing.markets.$country") : null;
        $forYou = collect();
        if ($market && ! $category && $q === '' && $posts->currentPage() === 1) {
            $forYou = BlogPost::query()->published()->with('category')
                ->where('target_countries', 'like', '%"'.$country.'"%')
                ->latest('published_at')->limit(3)->get();
        }

        return view('blog.index', compact('posts', 'categories', 'category', 'q', 'forYou', 'market'));
    }

    public function show(Request $request, string $slug, VisitorTracker $tracker)
    {
        $post = BlogPost::where('slug', $slug)->with(['category', 'author'])->firstOrFail();

        // Les brouillons ne sont visibles que par les admins (aperçu).
        if (! $post->isPublished()) {
            abort_unless($request->user()?->hasRole('admin'), 404);
        }

        // Compteur de lectures : une fois par session, hors robots et admins.
        $seenKey = 'blog_seen.'.$post->id;
        if ($post->isPublished() && ! $request->session()->has($seenKey)
            && ! $tracker->isBot($request) && ! $request->user()?->hasRole('admin')) {
            $request->session()->put($seenKey, true);
            $post->increment('views_count');
        }

        $related = BlogPost::query()
            ->published()
            ->whereKeyNot($post->id)
            ->when($post->blog_category_id, fn ($q) => $q->orderByRaw('blog_category_id = ? DESC', [$post->blog_category_id]))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
