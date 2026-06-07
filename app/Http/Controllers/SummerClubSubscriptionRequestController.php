<?php

namespace App\Http\Controllers;

use App\Models\SummerClubSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SummerClubSubscriptionRequestController extends Controller
{
    public function store(Request $request)
    {
        $packs = SummerClubSubscriptionRequest::packDefinitions();
        $subjects = SummerClubSubscriptionRequest::subjects();

        $validated = $request->validate([
            'pack_key' => ['required', 'string', Rule::in(array_keys($packs))],
            'parent_name' => ['required', 'string', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'selected_subjects' => ['nullable', 'array'],
            'selected_subjects.*' => ['string', Rule::in($subjects)],
        ]);

        $pack = $packs[$validated['pack_key']];
        $selectedSubjects = SummerClubSubscriptionRequest::subjectsForPack(
            $validated['pack_key'],
            $validated['selected_subjects'] ?? []
        );

        SummerClubSubscriptionRequest::create([
            'user_id' => $request->user()?->id,
            'parent_name' => $validated['parent_name'],
            'student_name' => $validated['student_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'pack_key' => $validated['pack_key'],
            'pack_name' => $pack['name'],
            'selected_subjects' => $selectedSubjects,
            'price' => $pack['price'],
            'duration_months' => $pack['duration_months'],
            'status' => 'pending',
        ]);

        $message = "Votre demande a été envoyée. L'équipe Zaytouna vous contactera pour confirmer l'abonnement.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
