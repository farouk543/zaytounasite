<?php

namespace App\Http\Controllers;

use App\Models\SummerClubExercise;
use App\Models\SummerClubQuiz;
use App\Models\SummerClubResource;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSend(Request $request)
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:180'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        // TODO: wire to a Mailable when SMTP is configured
        // Mail::to(config('mail.from.address'))->send(new ContactMail($request->all()));

        return back()->with('success', __('ui.pages.contact.sent'));
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function refunds()
    {
        return view('pages.refunds');
    }

    public function summerClub()
    {
        $resources = SummerClubResource::where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $quizzes = SummerClubQuiz::with('resource')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $exercises = SummerClubExercise::with('resource')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $catalogItems = collect()
            ->merge($resources->map(fn ($resource) => [
                'title' => $resource->title,
                'description' => $resource->description,
                'subject' => $resource->subject,
                'level' => $resource->level,
                'cover_image_path' => $resource->cover_image_path,
                'kind' => $resource->type === 'fiche' ? 'fiche' : 'formation',
                'label' => $resource->type === 'fiche' ? 'Fiche de revision' : 'Formation',
                'sort_order' => $resource->sort_order,
            ]))
            ->merge($quizzes->map(fn ($quiz) => [
                'title' => $quiz->title,
                'description' => $quiz->description,
                'subject' => $quiz->subject,
                'level' => $quiz->level,
                'cover_image_path' => $quiz->resource?->cover_image_path,
                'kind' => 'quiz',
                'label' => 'Quiz interactif',
                'sort_order' => $quiz->sort_order,
            ]))
            ->merge($exercises->map(fn ($exercise) => [
                'title' => $exercise->title,
                'description' => $exercise->description,
                'subject' => $exercise->subject,
                'level' => $exercise->level,
                'cover_image_path' => $exercise->cover_image_path ?: $exercise->resource?->cover_image_path,
                'kind' => 'exercice',
                'label' => 'Exercice interactif',
                'sort_order' => $exercise->sort_order,
            ]))
            ->sortBy('sort_order')
            ->values();

        return view('club-ete', [
            'resources' => $resources,
            'catalogItems' => $catalogItems,
        ]);
    }
}
