<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Track;
use App\Services\PriceResolver;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(private readonly PriceResolver $priceResolver)
    {
    }

    public function home()
    {
        return view('welcome');
    }

    public function catalog(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $trackSlug = (string) $request->get('track', '');
        $levelSlug = (string) $request->get('level', '');
        $branchSlug = (string) $request->get('branch', '');
        $subjectSlug = (string) $request->get('subject', '');
        $type = (string) $request->get('type', '');
        $sort = (string) $request->get('sort', 'new');

        $tracks = Track::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedTrack = $trackSlug !== ''
            ? Track::query()
                ->where('is_active', true)
                ->where('slug', $trackSlug)
                ->first()
            : null;

        $selectedLevel = $levelSlug !== ''
            ? Level::query()
                ->where('is_active', true)
                ->when($selectedTrack, fn ($q) => $q->where('track_id', $selectedTrack->id))
                ->where('slug', $levelSlug)
                ->first()
            : null;

        $levels = Level::query()
            ->where('is_active', true)
            ->when($selectedTrack, fn ($query) => $query->where('track_id', $selectedTrack->id))
            ->orderBy('sort_order')
            ->get();

        $branches = Branch::query()
            ->where('is_active', true)
            ->when($selectedLevel, fn ($query) => $query->where('level_id', $selectedLevel->id))
            ->orderBy('sort_order')
            ->get();

        $levelHasBranches = $selectedLevel
            ? Branch::query()
                ->where('is_active', true)
                ->where('level_id', $selectedLevel->id)
                ->exists()
            : false;

        $subjects = Subject::query()
            ->where('is_active', true)
            ->when($selectedTrack, function ($query) use ($selectedTrack) {
                $query->whereHas('level', fn ($q) => $q->where('track_id', $selectedTrack->id));
            })
            ->when($selectedLevel, function ($query) use ($selectedLevel, $levelHasBranches, $branchSlug) {
                $query->where('level_id', $selectedLevel->id);

                if ($levelHasBranches) {
                    if ($branchSlug !== '') {
                        $query->whereHas('branch', fn ($q) => $q->where('slug', $branchSlug));
                    } else {
                        $query->whereNotNull('branch_id');
                    }
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->orderBy('sort_order')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Courses query
        |--------------------------------------------------------------------------
        |
        | Si type = exercise, on vide volontairement les cours, parce qu'on veut
        | afficher uniquement les exercices indépendants.
        |
        */

        $coursesQuery = Course::query()
            ->where('is_published', true)
            ->whereDoesntHave('includedInPackItems')
            ->withCount('enrollments')
            ->with(['subject.branch.level.track', 'subject.level.track', 'prices'])
            ->when($type === 'exercise', function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                        ->orWhere('title_ar', 'like', "%{$q}%")
                        ->orWhere('title_en', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('description_ar', 'like', "%{$q}%")
                        ->orWhere('description_en', 'like', "%{$q}%");
                });
            })
            ->when($type !== '' && $type !== 'exercise', fn ($query) => $query->where('delivery_type', $type))
            ->when($trackSlug !== '', function ($query) use ($trackSlug) {
                $query->whereHas('subject.level.track', fn ($q) => $q->where('slug', $trackSlug));
            })
            ->when($levelSlug !== '', function ($query) use ($levelSlug) {
                $query->whereHas('subject.level', fn ($q) => $q->where('slug', $levelSlug));
            })
            ->when($branchSlug !== '', function ($query) use ($branchSlug) {
                $query->whereHas('subject.branch', fn ($q) => $q->where('slug', $branchSlug));
            })
            ->when($subjectSlug !== '', function ($query) use ($subjectSlug) {
                $query->whereHas('subject', fn ($q) => $q->where('slug', $subjectSlug));
            });

        if ($sort === 'price_asc') {
            $coursesQuery->orderBy('price_cents');
        } elseif ($sort === 'price_desc') {
            $coursesQuery->orderByDesc('price_cents');
        } elseif ($sort === 'popular') {
            $coursesQuery->orderByDesc('enrollments_count');
        } elseif ($sort === 'featured') {
            $coursesQuery->orderByDesc('is_featured')->orderByDesc('id');
        } else {
            $coursesQuery->orderByDesc('id');
        }

        $courses = $coursesQuery->paginate(12)->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Exercises query
        |--------------------------------------------------------------------------
        |
        | Le catalogue public doit afficher uniquement les exercices indépendants.
        |
        | Règle :
        | - course_id = null
        | - course_pack_item_id = null
        |
        | Les exercices liés à un cours ou à un pack ne s'affichent pas ici.
        | Ils restent accessibles depuis leur cours/pack.
        |
        */

        $exercisesQuery = Exercise::query()
            ->where('is_published', true)
            ->with('prices')
            ->whereNull('course_id')
            ->whereNull('course_pack_item_id')
            ->when(! in_array($type, ['', 'exercise'], true), function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                        ->orWhere('title_ar', 'like', "%{$q}%")
                        ->orWhere('title_en', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('description_ar', 'like', "%{$q}%")
                        ->orWhere('description_en', 'like', "%{$q}%")
                        ->orWhere('instructions', 'like', "%{$q}%")
                        ->orWhere('instructions_ar', 'like', "%{$q}%")
                        ->orWhere('instructions_en', 'like', "%{$q}%");
                });
            });

        if ($sort === 'price_asc') {
            $exercisesQuery->orderBy('price_cents')->orderByDesc('id');
        } elseif ($sort === 'price_desc') {
            $exercisesQuery->orderByDesc('price_cents')->orderByDesc('id');
        } elseif ($sort === 'featured') {
            $exercisesQuery->orderBy('sort_order')->orderByDesc('id');
        } elseif ($sort === 'new') {
            $exercisesQuery->orderByDesc('id');
        } else {
            $exercisesQuery->orderBy('sort_order')->orderByDesc('id');
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination exercices
        |--------------------------------------------------------------------------
        |
        | Si l'utilisateur filtre par "exercise", on pagine les exercices.
        | Sinon, on récupère seulement quelques exercices indépendants publiés
        | à afficher avec les cours.
        |
        */

        if ($type === 'exercise') {
            $exercises = $exercisesQuery
                ->paginate(12, ['*'], 'exercises_page')
                ->withQueryString();
        } else {
            $exercises = $exercisesQuery
                ->limit(12)
                ->get();
        }

        // Course cards resolve their own price (prices relation is eager-loaded above).
        // Exercise cards are rendered inline here, so pre-resolve their quotes.
        $exercisePriceQuotes = $this->priceResolver->resolveMany(
            $exercises instanceof \Illuminate\Contracts\Pagination\Paginator
                ? $exercises->getCollection()
                : $exercises
        );

        return view('catalog.index', compact(
            'tracks',
            'levels',
            'branches',
            'subjects',
            'courses',
            'exercises',
            'levelHasBranches',
            'exercisePriceQuotes'
        ));
    }

    public function show(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        // Si ce cours est inclus dans un pack, rediriger vers la page du pack
        if ($course->isSimpleCourse()) {
            $parentPack = \App\Models\CoursePackItem::where('linked_course_id', $course->id)
                ->with('pack')
                ->first()?->pack;

            if ($parentPack && $parentPack->is_published) {
                return redirect()->route('courses.show', $parentPack);
            }
        }

        $course->load(['subject.branch.level.track', 'subject.level.track', 'prices']);
        $course->loadCount('enrollments');

        $priceQuote = $this->priceResolver->resolve($course);

        $user = $request->user();
        $isPaid = (bool) ($course->is_paid ?? true);
        $hasAccess = ! $isPaid || ($user && $user->hasActiveEnrollmentForCourse($course->id));

        $cart = session('cart.items', []);
        $inCart = array_key_exists((string) $course->id, $cart);

        // Related courses: same subject first, then same level, limit 4
        $relatedCourses = Course::query()
            ->where('is_published', true)
            ->where('id', '!=', $course->id)
            ->withCount('enrollments')
            ->with(['subject.level.track'])
            ->when($course->subject_id, function ($q) use ($course) {
                $q->where(function ($inner) use ($course) {
                    $inner->where('subject_id', $course->subject_id)
                        ->orWhereHas('subject', fn ($sq) => $sq->where(
                            'level_id',
                            $course->subject?->level_id
                        ));
                });
            })
            ->orderByDesc('enrollments_count')
            ->limit(4)
            ->get();

        return view('courses.show', compact('course', 'isPaid', 'hasAccess', 'inCart', 'relatedCourses', 'priceQuote'));
    }
}