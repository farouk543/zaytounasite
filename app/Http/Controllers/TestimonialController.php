<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Notifications\TestimonialReceived;
use App\Support\AdminNotifier;
use App\Support\Markets;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Page publique « Laisser un avis » : l'avis arrive non publié dans le back-office (Témoignages).
 */
class TestimonialController extends Controller
{
    public function create(Request $request): View
    {
        $program = $request->query('programme');

        return view('reviews.create', [
            'programs' => array_keys(Testimonial::PROGRAMS),
            'program' => array_key_exists((string) $program, Testimonial::PROGRAMS) ? $program : null,
            'countries' => array_keys(Markets::all()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Piège à robots : champ caché qu'un humain laisse vide.
        if (filled($request->input('website'))) {
            return redirect()->route('reviews.create')->with('review_sent', true);
        }

        $data = $request->validate([
            'author_name' => ['required', 'string', 'min:2', 'max:80'],
            'author_role' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'size:2', 'alpha'],
            'program' => ['required', Rule::in(array_keys(Testimonial::PROGRAMS))],
            'rating' => ['required', 'integer', 'between:1,5'],
            'quote' => ['required', 'string', 'min:20', 'max:1200'],
            'contact' => ['nullable', 'string', 'max:150'],
            'consent' => ['accepted'],
        ]);

        $testimonial = Testimonial::create([
            ...collect($data)->except('consent')->map(fn ($v) => is_string($v) ? trim(strip_tags($v)) : $v)->all(),
            'country' => isset($data['country']) ? strtoupper($data['country']) : null,
            'source' => 'site',
            'is_published' => false,
            'received_at' => now(),
        ]);

        AdminNotifier::send(new TestimonialReceived($testimonial));

        return redirect()->route('reviews.create')->with('review_sent', true);
    }
}
