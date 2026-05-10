<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exercise;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $items = $this->cartItemsDetailed($request);
        $totals = $this->cartTotals($items);

        return view('cart.show', [
            'items' => $items,
            'totals' => $totals,
        ]);
    }

    public function add(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        // Si cours gratuit, pas besoin panier
        if (! ($course->is_paid ?? true)) {
            return redirect()->route('courses.show', $course);
        }

        $cart = session('cart.items', []);

        // qty = 1
        $cart[(string) $course->id] = 1;

        session(['cart.items' => $cart]);

        return back()->with('success', __('ui.cart_added') ?? 'Ajouté au panier.');
    }

    public function remove(Request $request, Course $course)
    {
        $cart = session('cart.items', []);

        unset($cart[(string) $course->id]);

        session(['cart.items' => $cart]);

        return back()->with('success', __('ui.cart_removed') ?? 'Retiré du panier.');
    }

    public function addExercise(Request $request, Exercise $exercise)
    {
        abort_unless($exercise->is_published, 404);

        // Sécurité : seuls les exercices indépendants peuvent être vendus seuls
        abort_unless(
            is_null($exercise->course_id) && is_null($exercise->course_pack_item_id),
            403,
            'Cet exercice est déjà lié à un cours ou à un pack.'
        );

        // Si exercice gratuit, pas besoin panier
        if (! ($exercise->is_paid ?? true)) {
            return redirect()->route('exercise.show', $exercise);
        }

        $cart = session('cart.exercises', []);

        // qty = 1
        $cart[(string) $exercise->id] = 1;

        session(['cart.exercises' => $cart]);

        return back()->with('success', 'Exercice ajouté au panier.');
    }

    public function removeExercise(Request $request, Exercise $exercise)
    {
        $cart = session('cart.exercises', []);

        unset($cart[(string) $exercise->id]);

        session(['cart.exercises' => $cart]);

        return back()->with('success', 'Exercice retiré du panier.');
    }

    public function clear(Request $request)
    {
        session()->forget([
            'cart.items',
            'cart.exercises',
        ]);

        return back()->with('success', __('ui.cart_cleared') ?? 'Panier vidé.');
    }

    private function cartItemsDetailed(Request $request): array
    {
        $courseCart = session('cart.items', []);
        $exerciseCart = session('cart.exercises', []);

        $items = [];

        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        */
        if ($courseCart) {
            $courseIds = array_map('intval', array_keys($courseCart));

            $courses = Course::query()
                ->whereIn('id', $courseIds)
                ->where('is_published', true)
                ->get()
                ->keyBy('id');

            foreach ($courseCart as $id => $qty) {
                $course = $courses->get((int) $id);

                if (! $course) {
                    continue;
                }

                $priceCents = (int) ($course->price_cents ?? 0);
                $qty = 1;

                $items[] = [
                    'type' => 'course',
                    'id' => $course->id,
                    'course' => $course,
                    'exercise' => null,
                    'title' => $course->title,
                    'qty' => $qty,
                    'price_cents' => $priceCents,
                    'line_total_cents' => $priceCents * $qty,
                    'currency' => $course->currency ?? 'TND',
                    'remove_route' => route('cart.remove', $course),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Independent exercises
        |--------------------------------------------------------------------------
        */
        if ($exerciseCart) {
            $exerciseIds = array_map('intval', array_keys($exerciseCart));

            $exercises = Exercise::query()
                ->whereIn('id', $exerciseIds)
                ->where('is_published', true)
                ->whereNull('course_id')
                ->whereNull('course_pack_item_id')
                ->get()
                ->keyBy('id');

            foreach ($exerciseCart as $id => $qty) {
                $exercise = $exercises->get((int) $id);

                if (! $exercise) {
                    continue;
                }

                $priceCents = (int) ($exercise->price_cents ?? 0);
                $qty = 1;

                $items[] = [
                    'type' => 'exercise',
                    'id' => $exercise->id,
                    'course' => null,
                    'exercise' => $exercise,
                    'title' => $exercise->title,
                    'qty' => $qty,
                    'price_cents' => $priceCents,
                    'line_total_cents' => $priceCents * $qty,
                    'currency' => $exercise->currency ?? 'TND',
                    'remove_route' => route('cart.removeExercise', $exercise),
                ];
            }
        }

        return $items;
    }

    private function cartTotals(array $items): array
    {
        $subtotal = 0;
        $currency = 'TND';

        foreach ($items as $item) {
            $subtotal += (int) $item['line_total_cents'];
            $currency = $item['currency'] ?? $currency;
        }

        return [
            'subtotal_cents' => $subtotal,
            'total_cents' => $subtotal,
            'currency' => $currency,
        ];
    }
}